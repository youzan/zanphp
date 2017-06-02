
-- table order
create table order(
  id int(10) unsigned not null default 0 comment 'id',
  orderNo varchar(32) not null default '' comment '订单号',
  customerName varchar(50) not null default '' comment ''
)engine=innodb;