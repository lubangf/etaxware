ALTER TABLE etaxware.tblgooddetails ADD projectId varchar(100) null after disabled;

ALTER TABLE etaxware.tblgooddetails ADD projectName varchar(100) null after projectId;