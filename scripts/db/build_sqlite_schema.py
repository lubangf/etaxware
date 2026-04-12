import re
import sqlite3
from pathlib import Path

INPUT_SQL = Path(r"c:/xampp/htdocs/etaxware/scripts/db/etaxware.sql")
OUTPUT_SQLITE = Path(r"c:/xampp/htdocs/etaxware/scripts/db/etaxware_schema.sqlite")
OUTPUT_SCHEMA_SQL = Path(r"c:/xampp/htdocs/etaxware/scripts/db/etaxware_schema_sqlite.sql")
OUTPUT_SUMMARY = Path(r"c:/xampp/htdocs/etaxware/scripts/db/etaxware_schema_summary.txt")


CREATE_RE = re.compile(r"^\s*CREATE\s+TABLE\s+`([^`]+)`\s*\(", re.IGNORECASE)
COLUMN_RE = re.compile(r'^\s*`([^`]+)`\s+([^\s,]+)(.*)$')
PK_RE = re.compile(r"PRIMARY\s+KEY\s*\(([^\)]+)\)", re.IGNORECASE)


def map_mysql_type(mysql_type: str) -> str:
    t = mysql_type.lower()
    if t.startswith(("tinyint", "smallint", "mediumint", "int", "bigint", "bit")):
        return "INTEGER"
    if t.startswith(("decimal", "numeric", "float", "double", "real")):
        return "REAL"
    if t.startswith(("char", "varchar", "tinytext", "text", "mediumtext", "longtext", "enum", "set", "json")):
        return "TEXT"
    if t.startswith(("date", "datetime", "timestamp", "time", "year")):
        return "TEXT"
    if t.startswith(("blob", "tinyblob", "mediumblob", "longblob", "binary", "varbinary")):
        return "BLOB"
    return "TEXT"


def clean_attrs(attrs: str) -> str:
    a = attrs
    a = re.sub(r"COMMENT\s+'[^']*'", "", a, flags=re.IGNORECASE)
    a = re.sub(r"COLLATE\s+\w+", "", a, flags=re.IGNORECASE)
    a = re.sub(r"CHARACTER\s+SET\s+\w+", "", a, flags=re.IGNORECASE)
    a = re.sub(r"ON\s+UPDATE\s+CURRENT_TIMESTAMP(?:\([^\)]*\))?", "", a, flags=re.IGNORECASE)
    a = re.sub(r"AUTO_INCREMENT", "", a, flags=re.IGNORECASE)
    a = re.sub(r"\s+", " ", a).strip()

    parts = []
    if re.search(r"NOT\s+NULL", a, flags=re.IGNORECASE):
        parts.append("NOT NULL")

    m = re.search(r"DEFAULT\s+('(?:''|[^'])*'|\([^\)]*\)|[^,\s]+)", a, flags=re.IGNORECASE)
    if m:
        default_val = m.group(1).strip()
        if default_val.upper() == "NULL":
            parts.append("DEFAULT NULL")
        else:
            parts.append(f"DEFAULT {default_val}")

    return " ".join(parts).strip()


def parse_tables(path: Path):
    tables = []
    current_name = None
    body = []

    with path.open("r", encoding="utf-8", errors="ignore") as fh:
        for line in fh:
            if current_name is None:
                m = CREATE_RE.search(line)
                if m:
                    current_name = m.group(1)
                    body = []
                continue

            if re.match(r"^\s*\)\s*", line):
                tables.append((current_name, body[:]))
                current_name = None
                body = []
                continue

            body.append(line.rstrip("\n"))

    return tables


def convert_table(name: str, body_lines):
    columns = []
    pk_columns = []

    for raw in body_lines:
        line = raw.strip().rstrip(",")
        if not line:
            continue

        if line.upper().startswith(("KEY ", "UNIQUE KEY", "FULLTEXT KEY", "SPATIAL KEY", "CONSTRAINT ")):
            continue

        pk_match = PK_RE.search(line)
        if pk_match:
            cols = [c.strip().strip("`") for c in pk_match.group(1).split(",")]
            pk_columns = cols
            continue

        m = COLUMN_RE.match(line)
        if not m:
            continue

        col_name, mysql_type, attrs = m.group(1), m.group(2), m.group(3)
        sqlite_type = map_mysql_type(mysql_type)
        cleaned = clean_attrs(attrs)
        col_def = f'"{col_name}" {sqlite_type}'
        if cleaned:
            col_def += f" {cleaned}"
        columns.append((col_name, sqlite_type, col_def))

    if not columns:
        return None

    if len(pk_columns) == 1:
        pk = pk_columns[0]
        updated = []
        for col_name, sqlite_type, col_def in columns:
            if col_name == pk:
                if sqlite_type == "INTEGER":
                    col_def = re.sub(r"\s+DEFAULT\s+[^\s]+", "", col_def, flags=re.IGNORECASE)
                    col_def = re.sub(r"\s+NOT\s+NULL", "", col_def, flags=re.IGNORECASE)
                    col_def += " PRIMARY KEY"
                else:
                    col_def += " PRIMARY KEY"
            updated.append((col_name, sqlite_type, col_def))
        columns = updated
    elif len(pk_columns) > 1:
        # Composite key support
        columns.append((None, None, "PRIMARY KEY (" + ", ".join([f'\"{c}\"' for c in pk_columns]) + ")"))

    ddl = "CREATE TABLE IF NOT EXISTS \"{}\" (\n    {}\n);".format(
        name,
        ",\n    ".join([c[2] for c in columns]),
    )
    return ddl


def main():
    if not INPUT_SQL.exists():
        raise FileNotFoundError(f"Input SQL not found: {INPUT_SQL}")

    tables = parse_tables(INPUT_SQL)
    ddl_statements = []
    for name, body in tables:
        ddl = convert_table(name, body)
        if ddl:
            ddl_statements.append((name, ddl))

    schema_sql = "\n\n".join(ddl for _, ddl in ddl_statements) + "\n"
    OUTPUT_SCHEMA_SQL.write_text(schema_sql, encoding="utf-8")

    if OUTPUT_SQLITE.exists():
        OUTPUT_SQLITE.unlink()

    conn = sqlite3.connect(str(OUTPUT_SQLITE))
    try:
        cur = conn.cursor()
        cur.execute("PRAGMA foreign_keys=OFF")
        for table_name, ddl in ddl_statements:
            try:
                cur.execute(ddl)
            except Exception as exc:
                print(f"Failed table: {table_name}")
                print(ddl)
                raise
        conn.commit()

        cur.execute("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
        table_names = [r[0] for r in cur.fetchall()]
    finally:
        conn.close()

    summary_lines = [
        "SQLite schema mirror generated from etaxware/scripts/db/etaxware.sql",
        f"Total tables: {len(ddl_statements)}",
        "",
        "Tables:",
    ] + table_names

    OUTPUT_SUMMARY.write_text("\n".join(summary_lines) + "\n", encoding="utf-8")

    print(f"Created SQLite DB: {OUTPUT_SQLITE}")
    print(f"Created schema SQL: {OUTPUT_SCHEMA_SQL}")
    print(f"Created summary: {OUTPUT_SUMMARY}")
    print(f"Converted tables: {len(ddl_statements)}")


if __name__ == "__main__":
    main()
