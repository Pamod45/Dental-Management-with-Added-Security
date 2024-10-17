create database pdms;

use pdms;

create table user(	userid varchar(6) primary key,
					password varchar(12) not null, 
                    usertype varchar(8) not null,
                    registereddate date,
                    loginstatus int );

 create table patient(	userid varchar(6) ,
						patientid varchar(6),
						dob date,
						firstname varchar(30),
						lastname varchar(30),
						email varchar(255),
                        address varchar(100),
                        contactno varchar(12));  
                   
create table appointment( 	appointmentid  varchar(6) primary key,
							patientid varchar(6),
                            doctorid varchar(6),
                            paymentid varchar(6),
                            paymentmethodid varchar(6),
                            status varchar(12),
                            appointmentcharges decimal(7,2),
                            appointmentslot varchar(20),
                            createddate date,
                            appointmentdate date,
                            queueno int ); 
                            
alter table appointment 
modify column createddate date ;
                            
                            
create table doctor (	doctorid varchar(6) primary key,
						userid varchar(6),
                        branchid varchar(6),
                        dob datetime,
                        firstname varchar(30),
                        lastname varchar(30),
                        email varchar(255),
                        address varchar(100),
                        contactno varchar(12),
                        category varchar(60)                       
					);
                            
create table medicalrecord ( medicalrecordid varchar(7) primary key,
							 doctorid varchar(6),
                             patientid varchar(6),
                             date date,
                             time datetime,
                             presentingcomplaints varchar(255),
                             treatments varchar(255),
                             specialnotes varchar(255)
							);
create table schedule ( availabilityid varchar(7) primary key,
						doctorid varchar(6),
						date date,
                        starttime varchar(10),
                        duration varchar(10));
                        
create table employee ( employeeid varchar(6) primary key,
						userid varchar(6),
						emptypeid varchar(6),
                        firstname varchar(30),
                        lastname varchar(30),
                        email varchar(255),
						dob datetime,
                        address varchar(100),
                        contactno varchar(12),
                        branchid varchar(6)
					   );
                       
create table branch (	branchid varchar(6) primary key,
						name varchar(50)
					);
                    
create table leavetype( leavetypeid varchar(6) primary key,
						name varchar(50),
                        description varchar(255)                        
						);

create table employeeleave( empleaveid varchar(6) primary key,
							employeeid varchar(6),
							leavetypeid varchar(6),
                            date datetime,
                            requestdate datetime,
                            status varchar(10),
                            reason varchar(255) 
							);

create table empcheckinout( employeeid varchar(6),
							checkinoutid varchar(7) primary key,
                            date datetime,
                            checkintime varchar(10),
                            checkouttime varchar(10));
                            
create table payment (paymentid varchar(6) primary key,
					  totalamount float,
                      paidamount float,
                      balance float);

create table paymentmethod (paymentmethodid varchar(6) primary key,
							name varchar(50),
                            description varchar(100));
                            
create table employee_type( emptypeid varchar(6) primary key,
							Position varchar(30),
                            salary float,
                            description varchar(255)
							);
                            
create table income ( incomeid varchar(6) primary key,
					  branchid varchar(6),
                      amount float,
                      description varchar(255),
                      date date
                      );
create table expense ( expenseid varchar(6) primary key,
					  branchid varchar(6),
                      amount float,
                      description varchar(255),
                      date date
                      );






                    
