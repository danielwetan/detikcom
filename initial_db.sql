create database detikcom;

use detikcom;

create table transactions (
	id int primary key auto_increment,
	invoice_id int not null,
	references_id varchar(255) not null,
	va_number int,
	item_name varchar(150) not null,
	amount int not null,
	status varchar(50) not null,
	payment_type varchar(50) not null,
	customer_name varchar(200) not null,
	merchant_id int not null
);