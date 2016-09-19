select count(*) from people where LENGTH(fullname) != CHAR_LENGTH(fullname);
select       *  from people where LENGTH(fullname) != CHAR_LENGTH(fullname);

create table temppeople (select * from people where LENGTH(fullname) != CHAR_LENGTH(fullname));
       
alter table temppeople modify temppeople.fullname varchar(255) character set latin1;       
alter table temppeople modify temppeople.fullname blob;
alter table temppeople modify temppeople.fullname varchar(255) character set utf8;
       
select * from temppeople where LENGTH(fullname) = CHAR_LENGTH(fullname);
delete   from temppeople where LENGTH(fullname) = CHAR_LENGTH(fullname);

replace into people (select * from temppeople);

