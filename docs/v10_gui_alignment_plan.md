# eTaxWare GUI Alignment Plan Against FTS v10 API

## Scope

This plan aligns GUI modules with ERP-facing API contracts in FTS v10 so values flow end-to-end:
GUI form -> controller/model persistence -> Utilities payload -> EFRIS response persistence -> GUI display

Source contract file:

- etaxware-api/api/FTS/v10/Api.php

## Extracted v10 Input Contracts

### Product: uploadproduct

Business fields (excluding metadata):

- PRODUCTCODE
- ITEMNAME
- ITEMID
- QTY
- CURRENCY
- COMMODITYCODE
- MEASUREUNITS
- STOCKPREWARNING
- HASEXCISEDUTYFLAG
- HAVEPIECEUNITSFLAG
- PIECEUNITSMEASUREUNIT
- PIECEUNITPRICE
- PACKAGESCALEVALUE
- PIECESCALEVALUE
- EXCISEDUTYCODE
- ALTMEASUREUNITS
- REMARKS
- SOURCEBRANCH
- DESTBRANCH

### Invoice: uploadinvoice

Business fields (excluding metadata):

- VOUCHERNUMBER
- VOUCHERREF
- VOUCHERTYPE
- VOUCHERTYPENAME
- VOUCHERNARRATION
- INDUSTRYCODE
- CURRENCY
- PRICEVATINCLUSIVE
- DEFAULTVATRATE
- VATAPPLICATIONLEVEL
- DEEMEDFLAG
- PROJECTID
- PROJECTNAME
- REASONS
- INVENTORIES
- SERVICES
- BUYERTIN
- BUYERNINBRN
- BUYERPASSPORTNUM
- BUYERLEGALNAME
- BUSINESSNAME
- BUYERADDRESS
- BUYEREMAIL
- MOBILEPHONE
- BUYERLINEPHONE
- BUYERPLACEOFBUSI
- BUYERTYPE
- BUYERCITIZENSHIP
- BUYERSECTOR
- BUYERREFERENCENO

### Credit Note: uploadcreditnote

Business fields (excluding metadata):

- Same set as invoice upload (includes DEEMEDFLAG)

### Debit Note: uploaddebitnote

Business fields (excluding metadata):

- Same set as invoice upload except DEEMEDFLAG is not consumed in v10 function field references

## Current GUI/Utility Baseline (High Level)

- Product excise/piece fields are already wired in product upload payload and persistence flows.
- Invoice upload payload now includes additional excise artifact hydration at goods level for excise products.
- Invoice/Credit/Debit need a full field-by-field parity pass versus v10 contract, especially around:
  - buyer profile fields
  - project and VAT application fields
  - inventory/services line serialization shape
  - reason and voucher semantics

## Implementation Sequence

1. Product parity audit against v10 uploadproduct contract
2. Invoice parity implementation (highest field surface)
3. Credit note parity implementation
4. Debit note parity implementation
5. Back-propagation (EFRIS response -> DB -> GUI view/edit)
6. Evidence: payload decode logs + DB checks + GUI round-trip checks

## Verification Checklist Per Module

- Form capture: field exists in create/edit UI
- Controller mapping: POST -> DB columns and defaults
- Utility mapping: DB/object -> EFRIS payload fields
- Response mapping: EFRIS response -> DB columns
- UI rendering: fields visible in list/view/edit where required
- Logs: encoded request/response include expected fields

## Next Action

Start with Invoice module parity because it has the largest contract surface and shared patterns reusable by Credit/Debit.
