CREATE TABLE IF NOT EXISTS `account_verification` (
	account_verificationid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	isverified INT comment 'Is user verified by admin? shouldn't be for patient. Just for nurses, doctors and more.', 
	verifiedby INT comment 'Who verified account.', 
	date_verified VARCHAR(255)
);

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$OMegI7CgS.BS8fQd8UE1S.JBb2U4h0KFGX3htF\/NLjbwQ2Q4phSBy"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

ALTER TABLE `account_verification` CHANGE COLUMN isverified isverified INT comment 'Is user verified by admin? should not be for patient. Just for nurses, doctors and more.';
CREATE TABLE IF NOT EXISTS `account_verification` (
	account_verificationid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	isverified INT comment 'Is user verified by admin? should not be for patient. Just for nurses, doctors and more.', 
	verifiedby INT comment 'Who verified account.', 
	date_verified VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS `photos` (
	photoid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	photo VARCHAR(255) , 
	date_submitted VARCHAR(255) , 
	date_updated VARCHAR(255) null
);
--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,roleid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:roleid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI1851","roleid0":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"0","password0":"$2y$10$\/nt\/qen7zsdMPbifpKIZ3.gWxj.KAsh7lOOQMTn5kYzlNHBg071OS","activation_code0":"11634"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"0","time_added0":"2019-10-14 5:41:02 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$alfhDAax09eE8.XXEAoRJuuiMDlVSJwmt4XLLu1jRuy1Qn05bajIG"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI8520","sexid0":"1","stateid0":"1","accounttypeid0":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$v74aIVGTxyLAfdl1FIgl3.WqTAiHIqgn.566G2fGbPYGH2z6CxEVy","activation_code0":"2989"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 5:53:03 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$ZG4AWGRsqAJQUDjR66qg0OZ1.ylWJdZ5i91Wk.RsuulLsxRVoz7Iu"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$FpWgn3LoN0UOIq4QqZE\/Xes26OzsU.zBB7jnWcef3TA0BKD4AwdYm"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI7951","sexid0":"1","stateid0":"1","accounttypeid0":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$hwBiNZ6HLuLjY3avCYsUC.w5E\/naPNwFMx9\/p6gFkix6R4.tzqXma","activation_code0":"65970"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 5:58:47 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$sXysaFQXW7GR5F572Wm\/cOrSoZxk1JbIoXe0JIEWU314nLIMJ7v6K"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI5601","sexid0":"1","stateid0":"1","accounttypeid0":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$vQDKR5.gIvxNHQ6AHZl6xe\/Vx6whZ1uIeqL3OpHbd9rYc2at61tN6","activation_code0":"49111"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:01:07 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:05:18 pm","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- INSERT INTO account_track (accountid,isloggedin,session_token,token_expires) VALUES (:accountid0,:isloggedin0,:session_token0,:token_expires0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","isloggedin0":"1","session_token0":"544734661449d7de85a72b0299a53e78b039a30b","token_expires0":"1571076447"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:05:26 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"bd51bbbc5daa98cc27b17c4ebf1f98378dbe8954","token_expires":"1571076530","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:06:50 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"8826b8ad10782696616ac74262a65a5af3849789","token_expires":"1571076552","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:07:12 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"7489083ec200f1ddcc2a5eb3a92e3d4f42ae83b6","token_expires":"1571076794","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 6:11:14 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571076805","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571076903","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571076961","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"995cf5ee1836d597b3d38018025c3a426541e431","token_expires":"1571085534","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 8:36:53 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571085540","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571085556","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:38:57 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:42:06 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:46:50 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:53:24 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:55:05 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:55:27 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:55:47 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:55:56 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:56:09 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:57:22 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:57:52 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 10:59:53 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 11:16:17 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 11:16:40 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 11:31:04 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- UPDATE security SET accountid = :accountid , password = :password , activation_code = :activation_code  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","password":"$2y$10$uI8PVXBlprLcpRgII1nBZ.xHOrKHvzcLD2NAS7h7JS.HiXdVFRcRO","activation_code":"49402","accountid0":"1"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 11:36:40 pm","activity0":"Password reset initiated. Account blocked due to password reset."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- UPDATE security SET accountid = :accountid , password = :password , activation_code = :activation_code  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","password":"$2y$10$xVjPS9tjm\/gz12asYlbTo.QbCXTj8GWa1YUEP5Jz3i6vAlXHJhAHG","activation_code":"28132","accountid0":"1"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-14 11:37:18 pm","activity0":"Activation was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"b6b8996daa30107a369e897a06311ef1485fe8dd","token_expires":"1571099116","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:23:16 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"1adc65abc148e0c8faca506a6baa59510148f9fb","token_expires":"1571099134","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:23:34 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"11660f073f3a9543da0af4f0a8024423d271d83b","token_expires":"1571099948","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:37:08 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:37:38 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:38:11 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:40:30 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:42:53 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 12:47:40 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:01:17 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:07:45 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:10:22 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:11:33 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:27:31 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:27:58 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:29:26 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:31:16 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_verification] Dumping data for table `account_verification`
--- SQL ---
--- INSERT INTO account_verification (accountid,isverified) VALUES (:accountid0,:isverified0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","isverified0":"0"}---
--- ENDBINDS ---
--- [END-account_verification]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:37:54 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"0bca5044e13e197dfdfb185af9fe6d68a8b888e8","token_expires":"1571103960","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:44:00 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"23d228cd365da339fec90584d59e3b192dd566d6","token_expires":"1571145067","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-15 1:09:07 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571145076","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0)
--- ENDSQL ---
--- BINDS ---
--- {"language0":""}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"calabar1"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- DELETE FROM languages WHERE languageid = :languageid 
--- ENDSQL ---
--- BINDS ---
--- {"languageid":"9"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"calabar1"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- UPDATE languages SET language = :language , languageid = :languageid  WHERE languageid = :languageid0 
--- ENDSQL ---
--- BINDS ---
--- {"language":"calabar1","languageid":"10","languageid0":"10"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- UPDATE languages SET language = :language , languageid = :languageid  WHERE languageid = :languageid0 
--- ENDSQL ---
--- BINDS ---
--- {"language":"calabar12","languageid":"10","languageid0":"10"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- DELETE FROM languages WHERE languageid = :languageid 
--- ENDSQL ---
--- BINDS ---
--- {"languageid":"10"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"915177599a0b6f49ebd1616da07b7c397467d7a8","token_expires":"1571238252","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-16 3:02:11 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571238263","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571238292","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571238301","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"f7c02aebd758032d81f07f79c16975a338b04d5d","token_expires":"1571292270","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 6:02:30 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571292282","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571292288","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"12d50b2390e5a78af6d2540e9c3de8bec8e839f9","token_expires":"1571292482","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 6:06:01 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571292487","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"0e0b7116be9d143a6bdccb51f88e6377e06c1c7c","token_expires":"1571295214","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 6:51:34 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295227","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295257","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295283","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295311","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295349","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295394","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295478","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"19b99d6097b8a6cdf703f335afeb239b69c8b774","token_expires":"1571295827","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 7:01:46 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571295844","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"177863c56e3d185cfa12b820aedbb5201774e9ba","token_expires":"1571296063","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 7:05:43 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296070","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296089","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296143","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296173","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296205","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296234","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296268","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296378","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"3f97b210f8d0eda19a35b0718f6fea53cac6ca86","token_expires":"1571296527","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 7:13:27 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296539","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296572","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"330cbd3f0a005eab1ed047a980b8dcb7c9967c81","token_expires":"1571296832","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 7:18:31 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296841","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571296886","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"71d16c99cd1c2753afd40e20bb2aa50f285971e2","token_expires":"1571297019","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 7:21:39 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571297030","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571297121","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"e40a712974729dd3e397576d84ef6a05f8d8a330","token_expires":"1571304348","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:23:48 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304357","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304358","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304421","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304437","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"6590c7f9da4c88f27b7dc11e929046db0668a008","token_expires":"1571304650","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:28:50 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304665","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304765","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304782","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304802","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304832","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304911","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571304973","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305026","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305068","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305089","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305124","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"31a0f6ae88ef489dd08293195e96c9741296e8fc","token_expires":"1571305271","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:39:11 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305277","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305346","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305373","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"00c9218a03f2bcb0dae5b6b81c56f481e9c903fc","token_expires":"1571305516","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:43:16 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305522","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305566","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305576","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305617","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305686","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305709","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305763","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305799","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305866","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571305893","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571306011","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"f9d18658ea509b5743cfc066bb960ade51ce6d4e","token_expires":"1571306276","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:55:55 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571306285","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571306299","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"5834d20088938e7979e3918e723f1e8f1389865d","token_expires":"1571307959","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 10:23:58 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571307966","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"5b5321e64fa3c877c6d690b72fe488ac71d37272","token_expires":"1571309116","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 10:43:16 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309121","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309225","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309309","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309375","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309432","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309449","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309475","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309493","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309600","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"436427ba96ddfcb867fb678fe8507cd18ab69715","token_expires":"1571309726","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 10:53:26 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"e321df745ebe594443de54585df457459d266fd6","token_expires":"1571310706","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 10:56:46 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309939","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571309958","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"4e6cd1acb84b03a7c891ab375c0428cda8f69751","token_expires":"1571310837","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 10:58:57 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571310068","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"6ade1513ff18c329a4b9c6ca2adaf87015040e7e","token_expires":"1571311250","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:05:50 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571310481","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571310525","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571310552","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"c986b0f08d5f14f78e130b5df4675d5d69f1442b","token_expires":"1571314736","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:18:56 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"e129915149c86893d45b6cf35e94480d1c045a55","token_expires":"1571312778","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:31:18 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571312013","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"b94945671aafad5e41155658d3aa8c407b993b76","token_expires":"1571313379","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:41:19 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571312603","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571312604","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571312641","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"0d3f6c6e79117d11a3c00907e2ad483fbb01b512","token_expires":"1571313756","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:47:30 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313030","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313046","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"d326608a116df3e7fa1f9a1fd98e0509fc8958de","token_expires":"1571314431","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 11:58:45 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313651","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313654","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313669","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313674","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571313722","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571314622","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571314694","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571314789","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"ba5678aa70428e401f71348ba5cdc788c24f201c","token_expires":"1571314832","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:05:26 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"3ee410ee29ce10e0f6fceae56bc8f4ed041ea309","token_expires":"1571314886","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:06:20 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"9ac26931452cb9cda04bea8bcd1fb913699da1a4","token_expires":"1571314944","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:07:17 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"e928523a8432b8d424ed63258ea12b9818fa3985","token_expires":"1571314973","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:07:47 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"0bdc5f36e5a3fe6ce20f32da606665a1873fb3ee","token_expires":"1571315064","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:09:18 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571314850","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571314855","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571316071","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571316870","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571316884","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571316895","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571316966","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317045","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"a1e8e5dcbc70b006da6e4cf9f11916eb9299a6b9","token_expires":"1571317330","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 12:57:03 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317342","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,home_address,mbbs_certificate) VALUES (:accountid0,:specializationid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:specializationid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317436","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,home_address,mbbs_certificate) VALUES (:accountid0,:specializationid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317455","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,home_address,mbbs_certificate) VALUES (:accountid0,:specializationid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317532","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,home_address,mbbs_certificate) VALUES (:accountid0,:specializationid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317607","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,specializationid,home_address,mbbs_certificate) VALUES (:accountid0,:specializationid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","specializationid0":"","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317624","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,home_address,mbbs_certificate) VALUES (:accountid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317726","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,home_address,mbbs_certificate) VALUES (:accountid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317786","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,home_address,mbbs_certificate) VALUES (:accountid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- INSERT INTO account_information (accountid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571317854","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571318050","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,home_address,mbbs_certificate) VALUES (:accountid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- INSERT INTO account_information (accountid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 1:09:04 pm","activity0":"Account information Submitted. Awaiting approval"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"2a59d380866209c61224a9cb7ce7c4be69a0ed89","token_expires":"1571318324","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 1:13:38 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571318343","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571318344","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571318455","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- UPDATE doctors SET accountid = :accountid , home_address = :home_address , mbbs_certificate = :mbbs_certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","home_address":"wuse 2 abuja","mbbs_certificate":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- UPDATE account_information SET accountid = :accountid , insitution_attended = :insitution_attended , present_place_of_work = :present_place_of_work , address_to_place_of_work = :address_to_place_of_work , stateid = :stateid , cityid = :cityid , years_of_experience = :years_of_experience , certificate = :certificate  WHERE accountid = :accountid1 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","insitution_attended":"MIT","present_place_of_work":"National Hopsital","address_to_place_of_work":"Plot 78 Nnamdi Azikuwe close","stateid":"1","cityid":"1","years_of_experience":"2","certificate":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png","accountid1":"1"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 1:15:49 pm","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571324126","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- UPDATE doctors SET accountid = :accountid , home_address = :home_address , mbbs_certificate = :mbbs_certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","home_address":"wuse 2 abuja","mbbs_certificate":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- UPDATE account_information SET accountid = :accountid , insitution_attended = :insitution_attended , present_place_of_work = :present_place_of_work , address_to_place_of_work = :address_to_place_of_work , stateid = :stateid , cityid = :cityid , years_of_experience = :years_of_experience , certificate = :certificate  WHERE accountid = :accountid1 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","insitution_attended":"MIT","present_place_of_work":"National Hopsital","address_to_place_of_work":"Plot 78 Nnamdi Azikuwe close","stateid":"1","cityid":"1","years_of_experience":"2","certificate":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png","accountid1":"1"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 2:50:20 pm","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571324143","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- UPDATE doctors SET accountid = :accountid , home_address = :home_address , mbbs_certificate = :mbbs_certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","home_address":"wuse 2 abuja","mbbs_certificate":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- UPDATE account_information SET accountid = :accountid , insitution_attended = :insitution_attended , present_place_of_work = :present_place_of_work , address_to_place_of_work = :address_to_place_of_work , stateid = :stateid , cityid = :cityid , years_of_experience = :years_of_experience , certificate = :certificate  WHERE accountid = :accountid1 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","insitution_attended":"MIT","present_place_of_work":"National Hopsital","address_to_place_of_work":"Plot 78 Nnamdi Azikuwe close","stateid":"1","cityid":"1","years_of_experience":"2","certificate":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png","accountid1":"1"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 2:50:37 pm","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571324154","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- UPDATE doctors SET accountid = :accountid , home_address = :home_address , mbbs_certificate = :mbbs_certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","home_address":"wuse 2 abuja","mbbs_certificate":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- UPDATE account_information SET accountid = :accountid , insitution_attended = :insitution_attended , present_place_of_work = :present_place_of_work , address_to_place_of_work = :address_to_place_of_work , stateid = :stateid , cityid = :cityid , years_of_experience = :years_of_experience , certificate = :certificate  WHERE accountid = :accountid1 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","insitution_attended":"MIT","present_place_of_work":"National Hopsital","address_to_place_of_work":"Plot 78 Nnamdi Azikuwe close","stateid":"1","cityid":"1","years_of_experience":"2","certificate":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png","accountid1":"1"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 2:50:48 pm","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571324399","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325031","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325234","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325243","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325280","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325351","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"9fe41c895398acc4884a10e35e3c8f2674d6d96e","token_expires":"1571325646","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 3:15:40 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325655","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571325687","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571329177","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571329180","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$K3x\/I98JQQ47Z7nzvDXRj.1nbJcMzdIB\/IP02WqMqGhldtuGTibVK"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

CREATE TABLE IF NOT EXISTS `nurses` (
	nurseid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	isverified INT default 0
);
--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI7333","sexid0":"1","stateid0":"1","accounttypeid0":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$1Eruun6PoYLPiE7LYNj9cO7jMOl6AtQWwGND5mI900xJebLZQDPAW","activation_code0":"38238"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 8:12:13 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 8:13:04 pm","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 8:13:41 pm","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 8:15:10 pm","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- INSERT INTO account_track (accountid,isloggedin,session_token,token_expires) VALUES (:accountid0,:isloggedin0,:session_token0,:token_expires0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","isloggedin0":"1","session_token0":"f587efa993d35e14ceb8758a010d422a0a093868","token_expires0":"1571346388"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:01:22 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571346396","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571346546","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571346626","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571346627","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571347082","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571348130","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571348612","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571348776","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571348805","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_languages] Dumping data for table `account_languages`
--- SQL ---
--- INSERT INTO account_languages (accountid,languageid) VALUES (:accountid0,:languageid0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","languageid0":"1"}---
--- ENDBINDS ---
--- [END-account_languages]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:41:39 pm","activity0":"You added English language"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571348816","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"7751e98b2e3483e46144d7cdcd1e0f0fa3869549","token_expires":"1571349656","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-17 9:55:50 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571349666","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571381967","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"162648f1ffa1d6821825734fa724c50e2f933911","token_expires":"1571382253","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 6:59:07 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382263","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-nurses] Dumping data for table `nurses`
--- SQL ---
--- INSERT INTO nurses (accountid) VALUES (:accountid0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1"}---
--- ENDBINDS ---
--- [END-nurses]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- INSERT INTO account_information (accountid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Nurse\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 6:59:17 am","activity0":"Account information Submitted. Awaiting approval"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382279","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382353","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- UPDATE account_information SET accountid = :accountid , insitution_attended = :insitution_attended , present_place_of_work = :present_place_of_work , address_to_place_of_work = :address_to_place_of_work , stateid = :stateid , cityid = :cityid , years_of_experience = :years_of_experience , certificate = :certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","insitution_attended":"MIT","present_place_of_work":"National Hopsital","address_to_place_of_work":"Plot 78 Nnamdi Azikuwe close","stateid":"1","cityid":"1","years_of_experience":"2","certificate":".\/api\/Nurse\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:00:47 am","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382357","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382457","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$RLt4safxp8bcGlGR9hHIs..VXVaaVvJPdnd48InuS4rTXTqv2meLu"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI1157","sexid0":"1","stateid0":"1","accounttypeid0":"4"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$b0BFuR.mZT9fN\/a5py7nu.6Jbh.u7duoMSWSSPVBFo29EsVKK3Du.","activation_code0":"50873"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:04:07 am","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:04:55 am","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- INSERT INTO account_track (accountid,isloggedin,session_token,token_expires) VALUES (:accountid0,:isloggedin0,:session_token0,:token_expires0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","isloggedin0":"1","session_token0":"3e79cb6c893c782fa5bf5f07172c86032ba0f926","token_expires0":"1571382608"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:05:02 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571382638","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383204","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383356","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383452","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383501","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-hospitals] Dumping data for table `hospitals`
--- SQL ---
--- INSERT INTO hospitals (accountid,hospital_name,address,stateid,cityid,cac_certificate) VALUES (:accountid0,:hospital_name0,:address0,:stateid0,:cityid0,:cac_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","hospital_name0":"National Hospital","address0":"Matiama Abuja","stateid0":"1","cityid0":"1","cac_certificate0":".\/api\/Hospital\/Uploads\/eb6f9742da3848482cfcd2b69f999e4d.png"}---
--- ENDBINDS ---
--- [END-hospitals]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:19:55 am","activity0":"Account information Submitted. Awaiting approval"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383505","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383522","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383536","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571383548","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384280","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-hospital_specializations] Dumping data for table `hospital_specializations`
--- SQL ---
--- INSERT INTO hospital_specializations (hospitalid) VALUES (:hospitalid0)
--- ENDSQL ---
--- BINDS ---
--- {"hospitalid0":"1"}---
--- ENDBINDS ---
--- [END-hospital_specializations]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:32:54 am","activity0":"You added  specialization"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384324","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-hospital_specializations] Dumping data for table `hospital_specializations`
--- SQL ---
--- INSERT INTO hospital_specializations (hospitalid) VALUES (:hospitalid0)
--- ENDSQL ---
--- BINDS ---
--- {"hospitalid0":"1"}---
--- ENDBINDS ---
--- [END-hospital_specializations]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:33:38 am","activity0":"You added  specialization"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384374","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384406","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384415","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384420","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384477","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-hospital_specializations] Dumping data for table `hospital_specializations`
--- SQL ---
--- INSERT INTO hospital_specializations (hospitalid,specialization) VALUES (:hospitalid0,:specialization0)
--- ENDSQL ---
--- BINDS ---
--- {"hospitalid0":"1","specialization0":"paediatrican"}---
--- ENDBINDS ---
--- [END-hospital_specializations]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:36:11 am","activity0":"You added paediatrican specialization"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571384586","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"506624dc6d454fe668c8be40fa9f416c5e67bf68","token_expires":"1571413989","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 3:48:03 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_types] Dumping data for table `account_types`
--- SQL ---
--- INSERT INTO account_types (accounttype) VALUES (:accounttype0),(:accounttype1),(:accounttype2),(:accounttype3),(:accounttype4),(:accounttype5),(:accounttype6),(:accounttype7)
--- ENDSQL ---
--- BINDS ---
--- {"accounttype0":"Doctor","accounttype1":"Nurse","accounttype2":"Pharmacy","accounttype3":"Hospital","accounttype4":"Ambulance","accounttype5":"Lab","accounttype6":"Patient","accounttype7":"Administrator"}---
--- ENDBINDS ---
--- [END-account_types]

--- [BEGIN-administrators] Dumping data for table `administrators`
--- SQL ---
--- INSERT INTO administrators (firstname,lastname,telephone,email,username,password) VALUES (:firstname0,:lastname0,:telephone0,:email0,:username0,:password0)
--- ENDSQL ---
--- BINDS ---
--- {"firstname0":"ifeanyi","lastname0":"amadi","telephone0":"07066156036","email0":"helloamadiify@gmail.com","username0":"admin","password0":"$2y$10$iCN9ITzP1yjVvimMfa9Sp.AHI2hgvjyQqPaUjomKXrx2qosjy7Npy"}---
--- ENDBINDS ---
--- [END-administrators]

--- [BEGIN-cities] Dumping data for table `cities`
--- SQL ---
--- INSERT INTO cities (stateid,city) VALUES (:stateid0,:city0),(:stateid1,:city1)
--- ENDSQL ---
--- BINDS ---
--- {"stateid0":"1","city0":"Central District","stateid1":"1","city1":"FCT"}---
--- ENDBINDS ---
--- [END-cities]

--- [BEGIN-consultation_types] Dumping data for table `consultation_types`
--- SQL ---
--- INSERT INTO consultation_types (consultationtype) VALUES (:consultationtype0),(:consultationtype1)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtype0":"general","consultationtype1":"specialized"}---
--- ENDBINDS ---
--- [END-consultation_types]

--- [BEGIN-countries] Dumping data for table `countries`
--- SQL ---
--- INSERT INTO countries (country) VALUES (:country0)
--- ENDSQL ---
--- BINDS ---
--- {"country0":"Nigeria"}---
--- ENDBINDS ---
--- [END-countries]

--- [BEGIN-groups] Dumping data for table `groups`
--- SQL ---
--- INSERT INTO groups (accounttypeid,group_name) VALUES (:accounttypeid0,:group_name0),(:accounttypeid1,:group_name1),(:accounttypeid2,:group_name2),(:accounttypeid3,:group_name3),(:accounttypeid4,:group_name4),(:accounttypeid5,:group_name5),(:accounttypeid6,:group_name6),(:accounttypeid7,:group_name7),(:accounttypeid8,:group_name8),(:accounttypeid9,:group_name9)
--- ENDSQL ---
--- BINDS ---
--- {"accounttypeid0":"1","group_name0":"online","accounttypeid1":"1","group_name1":"home service","accounttypeid2":"2","group_name2":"minor wound dressing","accounttypeid3":"2","group_name3":"others","accounttypeid4":"3","group_name4":"delivery","accounttypeid5":"3","group_name5":"pickup","accounttypeid6":"5","group_name6":"post-paid","accounttypeid7":"5","group_name7":"pre-paid","accounttypeid8":"6","group_name8":"x-ray services","accounttypeid9":"6","group_name9":"laboratory"}---
--- ENDBINDS ---
--- [END-groups]

--- [BEGIN-languages] Dumping data for table `languages`
--- SQL ---
--- INSERT INTO languages (language) VALUES (:language0),(:language1),(:language2),(:language3),(:language4),(:language5),(:language6)
--- ENDSQL ---
--- BINDS ---
--- {"language0":"English","language1":"Igbo","language2":"French","language3":"Hausa","language4":"Yoruba","language5":"Spanish","language6":"Calabar"}---
--- ENDBINDS ---
--- [END-languages]

CREATE TABLE IF NOT EXISTS `pharmacytypes` (
	pharmacytypeid BIGINT(20) auto_increment primary key, 
	pharmacytype VARCHAR(255)
);
--- [BEGIN-platforms] Dumping data for table `platforms`
--- SQL ---
--- INSERT INTO platforms (platform,token) VALUES (:platform0,:token0),(:platform1,:token1)
--- ENDSQL ---
--- BINDS ---
--- {"platform0":"web","token0":"fadca654b5afbfbe4e262a36eb17c8af","platform1":"web-admin","token1":"2946aba47ec79a2b65c81d21248701c0"}---
--- ENDBINDS ---
--- [END-platforms]

--- [BEGIN-sex] Dumping data for table `sex`
--- SQL ---
--- INSERT INTO sex (sex) VALUES (:sex0),(:sex1)
--- ENDSQL ---
--- BINDS ---
--- {"sex0":"male","sex1":"female"}---
--- ENDBINDS ---
--- [END-sex]

--- [BEGIN-specializations] Dumping data for table `specializations`
--- SQL ---
--- INSERT INTO specializations (consultationtypeid,specialization) VALUES (:consultationtypeid0,:specialization0),(:consultationtypeid1,:specialization1),(:consultationtypeid2,:specialization2),(:consultationtypeid3,:specialization3)
--- ENDSQL ---
--- BINDS ---
--- {"consultationtypeid0":"2","specialization0":"paediatrican","consultationtypeid1":"2","specialization1":"gynaecologist","consultationtypeid2":"2","specialization2":"dentist","consultationtypeid3":"2","specialization3":"other"}---
--- ENDBINDS ---
--- [END-specializations]

--- [BEGIN-states] Dumping data for table `states`
--- SQL ---
--- INSERT INTO states (state,countryid) VALUES (:state0,:countryid0),(:state1,:countryid1)
--- ENDSQL ---
--- BINDS ---
--- {"state0":"Abuja","countryid0":"1","state1":"Lagos","countryid1":"1"}---
--- ENDBINDS ---
--- [END-states]

--- [BEGIN-pharmacytypes] Dumping data for table `pharmacytypes`
--- SQL ---
--- INSERT INTO pharmacytypes (pharmacytype) VALUES (:pharmacytype0),(:pharmacytype1),(:pharmacytype2),(:pharmacytype3),(:pharmacytype4),(:pharmacytype5),(:pharmacytype6),(:pharmacytype7),(:pharmacytype8),(:pharmacytype9),(:pharmacytype10),(:pharmacytype11),(:pharmacytype12),(:pharmacytype13),(:pharmacytype14),(:pharmacytype15),(:pharmacytype16),(:pharmacytype17),(:pharmacytype18),(:pharmacytype19),(:pharmacytype20),(:pharmacytype21),(:pharmacytype22),(:pharmacytype23),(:pharmacytype24),(:pharmacytype25),(:pharmacytype26),(:pharmacytype27)
--- ENDSQL ---
--- BINDS ---
--- {"pharmacytype0":"Analgesics","pharmacytype1":"Anti-Inflammatory","pharmacytype2":"Body Pain","pharmacytype3":"Head Ache & Migraine","pharmacytype4":"Colic & Griping Pain","pharmacytype5":"Rheumatics & Arthritis","pharmacytype6":"Stomach Ache","pharmacytype7":"Digestive Care","pharmacytype8":"Antacids","pharmacytype9":"Anti-Diarrheal","pharmacytype10":"Colon Cleanser","pharmacytype11":"De-Wormers","pharmacytype12":"Laxatives","pharmacytype13":"Probiotics","pharmacytype14":"Cough & Cold","pharmacytype15":"Asthma","pharmacytype16":"Catarrh","pharmacytype17":"Expectorants","pharmacytype18":"Nasal Congestion","pharmacytype19":"Sore Throat","pharmacytype20":"Cough","pharmacytype21":"Supplements","pharmacytype22":"50 Plus (+)","pharmacytype23":"Bones & Joints","pharmacytype24":"Liver Formula","pharmacytype25":"Fertility Formula","pharmacytype26":"Menopause Formula","pharmacytype27":"Prostate Formula"}---
--- ENDBINDS ---
--- [END-pharmacytypes]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- INSERT INTO account (username,firstname,lastname,email,telephone,address,referid,refercode,sexid,stateid,accounttypeid) VALUES (:username0,:firstname0,:lastname0,:email0,:telephone0,:address0,:referid0,:refercode0,:sexid0,:stateid0,:accounttypeid0)
--- ENDSQL ---
--- BINDS ---
--- {"username0":"wekiwork","firstname0":"ifeanyi","lastname0":"amadi","email0":"helloamadiify@gmail.com","telephone0":"07066156036","address0":"no 42 onisha cresent wuse 2 ","referid0":"0","refercode0":"MEDI1517","sexid0":"1","stateid0":"1","accounttypeid0":"3"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-security] Dumping data for table `security`
--- SQL ---
--- INSERT INTO security (accountid,password,activation_code) VALUES (:accountid0,:password0,:activation_code0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","password0":"$2y$10$LTPHX4\/2KjOrkBlQRfGYV.IpS6TFW0BC1OAqzsh.0v2pAkxPXsV22","activation_code0":"19797"}---
--- ENDBINDS ---
--- [END-security]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 4:20:56 pm","activity0":"Created an account. Activation code sent to [helloamadiify@gmail.com] for verification."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isblocked = :isblocked , isverified = :isverified  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isblocked":"0","isverified":"1","accountid":"1"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 4:22:00 pm","activity0":"Verification was successful, account unlocked."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- INSERT INTO account_track (accountid,isloggedin,session_token,token_expires) VALUES (:accountid0,:isloggedin0,:session_token0,:token_expires0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","isloggedin0":"1","session_token0":"c4333c0e574d5e0093d4ff76fd6e4d98904f1fcf","token_expires0":"1571416033"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 4:22:06 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571416110","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571428844","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571428872","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-pharmacies] Dumping data for table `pharmacies`
--- SQL ---
--- INSERT INTO pharmacies (accountid,pharmacy_name,address,stateid,cityid,cac_certificate) VALUES (:accountid0,:pharmacy_name0,:address0,:stateid0,:cityid0,:cac_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","pharmacy_name0":"National Health Pharmacy","address0":"Matiama Abuja","stateid0":"1","cityid0":"1","cac_certificate0":".\/api\/Pharmacy\/Uploads\/eb6f9742da3848482cfcd2b69f999e4d.png"}---
--- ENDBINDS ---
--- [END-pharmacies]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:56:06 pm","activity0":"Account information Submitted. Awaiting approval"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571428875","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-pharmacies] Dumping data for table `pharmacies`
--- SQL ---
--- UPDATE pharmacies SET accountid = :accountid , pharmacy_name = :pharmacy_name , address = :address , stateid = :stateid , cityid = :cityid , cac_certificate = :cac_certificate  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","pharmacy_name":"National Health Pharmacy","address":"Matiama Abuja","stateid":"1","cityid":"1","cac_certificate":".\/api\/Pharmacy\/Uploads\/eb6f9742da3848482cfcd2b69f999e4d.png","accountid0":"1"}---
--- ENDBINDS ---
--- [END-pharmacies]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-18 7:56:09 pm","activity0":"Account information updated successfully"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"5436896a04831a597ba62bd984b3d771b1e88891","token_expires":"1571450238","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 1:52:11 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571450250","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464114","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464115","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464181","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464247","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464260","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464269","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-pharmacy_type_list] Dumping data for table `pharmacy_type_list`
--- SQL ---
--- INSERT INTO pharmacy_type_list (pharmacytypeid,pharmacyid) VALUES (:pharmacytypeid0,:pharmacyid0)
--- ENDSQL ---
--- BINDS ---
--- {"pharmacytypeid0":"1","pharmacyid0":"1"}---
--- ENDBINDS ---
--- [END-pharmacy_type_list]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 5:46:03 am","activity0":"You added Analgesics type"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464338","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464384","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464409","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464416","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-pharmacy_type_list] Dumping data for table `pharmacy_type_list`
--- SQL ---
--- UPDATE pharmacy_type_list SET pharmacytypelistid = :pharmacytypelistid , pharmacytypeid = :pharmacytypeid  WHERE pharmacytypelistid = :pharmacytypelistid0 
--- ENDSQL ---
--- BINDS ---
--- {"pharmacytypelistid":"1","pharmacytypeid":"1","pharmacytypelistid0":"1"}---
--- ENDBINDS ---
--- [END-pharmacy_type_list]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 5:48:30 am","activity0":"You updated pharmacy type Analgesics"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"f58e873979fbd3382301f162170dce91613bf47e","token_expires":"1571464704","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 5:53:18 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464713","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464822","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571464935","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"a61eeb332635e03c2d8dc1f7c91bb1b48975dd2f","token_expires":"1571491900","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 1:26:33 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571491906","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571491953","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571491981","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492013","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492096","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492137","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492151","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492198","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 1:31:32 pm","activity0":"Motiu Capsule added to pharmacy inventory."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-drugs] Dumping data for table `drugs`
--- SQL ---
--- INSERT INTO drugs (drug_name,description,accountid,pharmacyid,pharmacytypeid,prescribed,price) VALUES (:drug_name0,:description0,:accountid0,:pharmacyid0,:pharmacytypeid0,:prescribed0,:price0)
--- ENDSQL ---
--- BINDS ---
--- {"drug_name0":"Motiu Capsule","description0":"Great medicine for body pain","accountid0":"1","pharmacyid0":"1","pharmacytypeid0":"3","prescribed0":"1","price0":"4000"}---
--- ENDBINDS ---
--- [END-drugs]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492235","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492696","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492708","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492719","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492764","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 1:40:58 pm","activity0":"Motiu Capsule updated in pharmacy inventory."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-drugs] Dumping data for table `drugs`
--- SQL ---
--- UPDATE drugs SET drug_name = :drug_name , description = :description , accountid = :accountid , pharmacytypeid = :pharmacytypeid , prescribed = :prescribed , price = :price , drugid = :drugid  WHERE drugid = :drugid0 
--- ENDSQL ---
--- BINDS ---
--- {"drug_name":"Motiu Capsule","description":"Great medicine for body pain","accountid":"1","pharmacytypeid":"3","prescribed":"1","price":"4000","drugid":"1","drugid0":"1"}---
--- ENDBINDS ---
--- [END-drugs]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492770","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571492782","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 1:41:16 pm","activity0":"Motiu Capsule updated in pharmacy inventory."}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-drugs] Dumping data for table `drugs`
--- SQL ---
--- UPDATE drugs SET drug_name = :drug_name , description = :description , accountid = :accountid , pharmacytypeid = :pharmacytypeid , prescribed = :prescribed , price = :price , drugid = :drugid  WHERE drugid = :drugid0 
--- ENDSQL ---
--- BINDS ---
--- {"drug_name":"Motiu Capsule","description":"Great medicine for body pain","accountid":"1","pharmacytypeid":"3","prescribed":"1","price":"4000","drugid":"1","drugid0":"1"}---
--- ENDBINDS ---
--- [END-drugs]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497477","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497523","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497530","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497534","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497545","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"243e962a6eef89152c4e4035a008da73fc912ae6","token_expires":"1571497880","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 3:06:14 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497887","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497892","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497904","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497930","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571497936","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"f400e8a70ab170074c3cf7fa4e9c83714cc839cb","token_expires":"1571501338","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:03:52 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501371","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-doctors] Dumping data for table `doctors`
--- SQL ---
--- INSERT INTO doctors (accountid,home_address,mbbs_certificate) VALUES (:accountid0,:home_address0,:mbbs_certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","home_address0":"wuse 2 abuja","mbbs_certificate0":".\/api\/Doctor\/Uploads\/739eec7fda9d889277b4b9f0008afc48.png"}---
--- ENDBINDS ---
--- [END-doctors]

--- [BEGIN-account_information] Dumping data for table `account_information`
--- SQL ---
--- INSERT INTO account_information (accountid,insitution_attended,present_place_of_work,address_to_place_of_work,stateid,cityid,years_of_experience,certificate) VALUES (:accountid0,:insitution_attended0,:present_place_of_work0,:address_to_place_of_work0,:stateid0,:cityid0,:years_of_experience0,:certificate0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","insitution_attended0":"MIT","present_place_of_work0":"National Hopsital","address_to_place_of_work0":"Plot 78 Nnamdi Azikuwe close","stateid0":"1","cityid0":"1","years_of_experience0":"2","certificate0":".\/api\/Doctor\/Uploads\/b3fc36b283debf5a14eb26f87cbe4680.png"}---
--- ENDBINDS ---
--- [END-account_information]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:04:25 pm","activity0":"Account information Submitted. Awaiting approval"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501433","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501464","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501486","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501536","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501548","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501574","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501596","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501628","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501673","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501699","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501710","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571501837","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571502422","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:21:56 pm","activity0":"You prescribed #{0} drug to Ifeanyi Amadi"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:21:56 pm","activity0":"DR. Ifeanyi Amadi prescribed #{0} drug to you"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-prescribtion_codes] Dumping data for table `prescribtion_codes`
--- SQL ---
--- INSERT INTO prescribtion_codes (drugs,accountid,doctorid,prescribtion_code) VALUES (:drugs0,:accountid0,:doctorid0,:prescribtion_code0)
--- ENDSQL ---
--- BINDS ---
--- {"drugs0":"[{\\\"drugid\\\":1,\\\"note\\\":\\\"Take 2 every morning\\\"}]","accountid0":"1","doctorid0":"1","prescribtion_code0":"PRE115715"}---
--- ENDBINDS ---
--- [END-prescribtion_codes]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571502537","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571502560","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571502604","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:24:58 pm","activity0":"You prescribed #{1} drug to Ifeanyi Amadi"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-19 4:24:58 pm","activity0":"DR. Ifeanyi Amadi prescribed #{1} drug to you"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-prescribtion_codes] Dumping data for table `prescribtion_codes`
--- SQL ---
--- INSERT INTO prescribtion_codes (drugs,accountid,doctorid,prescribtion_code) VALUES (:drugs0,:accountid0,:doctorid0,:prescribtion_code0)
--- ENDSQL ---
--- BINDS ---
--- {"drugs0":"[{\\\"drugid\\\":1,\\\"note\\\":\\\"Take 2 every morning\\\"}]","accountid0":"1","doctorid0":"1","prescribtion_code0":"PRE115715"}---
--- ENDBINDS ---
--- [END-prescribtion_codes]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"89affea1b9895e50b1cfd568583f9353e7ffa958","token_expires":"1571580518","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 2:03:32 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580528","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580573","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580588","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580718","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580723","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571580737","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571582630","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571582683","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571587507","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571587695","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571592340","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571592361","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571592373","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"8af7eb90187a5706a9ed40a8af8b0af194a0e442","token_expires":"1571592643","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 5:25:37 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571592658","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

CREATE TABLE IF NOT EXISTS `pharmacy_orders` (
	pharmacyorderid BIGINT(20) auto_increment primary key, 
	pharmacyid INT , 
	drugid INT , 
	accountid INT , 
	quantity INT , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default 'CURRENT_TIMESTAMP' sting null
);
ALTER TABLE `pharmacy_orders` ADD datecompleted VARCHAR(255) null AFTER dateissued;
CREATE TABLE IF NOT EXISTS `pharmacy_orders` (
	pharmacyorderid BIGINT(20) auto_increment primary key, 
	pharmacyid INT , 
	drugid INT , 
	accountid INT , 
	quantity INT , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default 'CURRENT_TIMESTAMP', 
	datecompleted VARCHAR(255) null
);
ALTER TABLE `pharmacy_orders` CHANGE COLUMN dateissued dateissued DATETIME default CURRENT_TIMESTAMP;
CREATE TABLE IF NOT EXISTS `pharmacy_orders` (
	pharmacyorderid BIGINT(20) auto_increment primary key, 
	pharmacyid INT , 
	drugid INT , 
	accountid INT , 
	quantity INT , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default CURRENT_TIMESTAMP, 
	datecompleted VARCHAR(255) null
);
--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571595131","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571595154","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571595175","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-pharmacy_orders] Dumping data for table `pharmacy_orders`
--- SQL ---
--- INSERT INTO pharmacy_orders (pharmacyid,drugid,quantity,accountid) VALUES (:pharmacyid0,:drugid0,:quantity0,:accountid0)
--- ENDSQL ---
--- BINDS ---
--- {"pharmacyid0":"1","drugid0":"1","quantity0":"1","accountid0":"1"}---
--- ENDBINDS ---
--- [END-pharmacy_orders]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 6:07:49 pm","activity0":"You have a new order from Ifeanyi Amadi"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 6:07:49 pm","activity0":"You placed an order with National Health Pharmacy pharmacy."}---
--- ENDBINDS ---
--- [END-activities]

ALTER TABLE `pharmacy_orders` ADD groupid INT AFTER datecompleted;
CREATE TABLE IF NOT EXISTS `pharmacy_orders` (
	pharmacyorderid BIGINT(20) auto_increment primary key, 
	pharmacyid INT , 
	drugid INT , 
	accountid INT , 
	quantity INT , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default CURRENT_TIMESTAMP, 
	datecompleted VARCHAR(255) null, 
	groupid INT
);
--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"a797fc642f39e4358c6985d376789fc3dfd077f0","token_expires":"1571596781","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 6:34:35 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571596788","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571596800","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571596826","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571596847","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597062","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597077","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597129","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597140","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597147","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597152","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597258","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597265","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597273","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571597331","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

CREATE TABLE IF NOT EXISTS `orders` (
	orderid BIGINT(20) auto_increment primary key, 
	paymentid INT default 0, 
	accountid INT , 
	groupid INT , 
	fromid INT comment 'user id', 
	status VARCHAR(255) default 'pending', 
	remark TEXT null, 
	dateissued VARCHAR(255) , 
	dateclosed VARCHAR(255) null
);
CREATE TABLE IF NOT EXISTS `payments` (
	paymentid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	amount VARCHAR(255) , 
	txref VARCHAR(255) , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default CURRENT_TIMESTAMP
);
--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"a19e6bec8fce3eb2830cfdafbb9d5492ebff2272","token_expires":"1571615423","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-20 11:45:16 pm","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615431","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615448","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615503","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615511","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615513","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

ALTER TABLE `payments` ADD narration TEXT AFTER dateissued;
CREATE TABLE IF NOT EXISTS `payments` (
	paymentid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	amount VARCHAR(255) , 
	txref VARCHAR(255) , 
	status VARCHAR(255) default 'pending', 
	dateissued DATETIME default CURRENT_TIMESTAMP, 
	narration TEXT
);
--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615959","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615978","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571615990","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616003","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616021","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616071","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616086","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616087","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616097","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616155","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616306","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-21 12:00:01 am","activity0":"You initiated a payment on txref #9ee35af2544c378d123a8ffe6a9a90"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-payments] Dumping data for table `payments`
--- SQL ---
--- INSERT INTO payments (accountid,txref,amount) VALUES (:accountid0,:txref0,:amount0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","txref0":"9ee35af2544c378d123a8ffe6a9a90","amount0":"1000"}---
--- ENDBINDS ---
--- [END-payments]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616322","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616712","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571616732","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET accountid = :accountid , isloggedin = :isloggedin , session_token = :session_token , token_expires = :token_expires  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"1","isloggedin":"1","session_token":"f91472875ef269827ef1e06de1b63b45fcfbf9c6","token_expires":"1571617061","accountid0":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-21 12:12:35 am","activity0":"New sign in request was successful from Web browser"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571617068","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571617078","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571617136","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

--- [BEGIN-payments] Dumping data for table `payments`
--- SQL ---
--- UPDATE payments SET txref = :txref , accountid = :accountid , status = :status  WHERE txref = :txref0 
--- ENDSQL ---
--- BINDS ---
--- {"txref":"9ee35af2544c378d123a8ffe6a9a90","accountid":"1","status":"success","txref0":"9ee35af2544c378d123a8ffe6a9a90"}---
--- ENDBINDS ---
--- [END-payments]

--- [BEGIN-activities] Dumping data for table `activities`
--- SQL ---
--- INSERT INTO activities (accountid,time_added,activity) VALUES (:accountid0,:time_added0,:activity0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"1","time_added0":"2019-10-21 12:13:50 am","activity0":"Your txref\/9ee35af2544c378d123a8ffe6a9a90 payment was successful"}---
--- ENDBINDS ---
--- [END-activities]

--- [BEGIN-account_track] Dumping data for table `account_track`
--- SQL ---
--- UPDATE account_track SET token_expires = :token_expires  WHERE accountid = :accountid 
--- ENDSQL ---
--- BINDS ---
--- {"token_expires":"1571618178","accountid":"1"}---
--- ENDBINDS ---
--- [END-account_track]

CREATE TABLE IF NOT EXISTS `labs` (
	labid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	groupid INT , 
	isverified INT default 0
);
ALTER TABLE `labs` ADD lab_name VARCHAR(255) AFTER isverified;
ALTER TABLE `labs` ADD address VARCHAR(255) AFTER lab_name;
ALTER TABLE `labs` ADD cac_certificate VARCHAR(255) AFTER address;
ALTER TABLE `labs` ADD stateid INT AFTER cac_certificate;
ALTER TABLE `labs` ADD cityid INT AFTER stateid;
CREATE TABLE IF NOT EXISTS `labs` (
	labid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	groupid INT , 
	isverified INT default 0, 
	lab_name VARCHAR(255) , 
	address VARCHAR(255) , 
	cac_certificate VARCHAR(255) , 
	stateid INT , 
	cityid INT
);
CREATE TABLE IF NOT EXISTS `web_photo` (
	web_photoid BIGINT(20) auto_increment primary key, 
	accountid INT , 
	cover_image VARCHAR(255) null, 
	profile_image VARCHAR(255) null
);
--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/a4acc986d1f86889a51087e65867bfd0ec1916d1.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/2596decaa03ab02c94cdba78797e1f9d5de6750d.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/fe55c191c8305aeb30e39452e4647698afaa5fe9.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/45f3389efbbf87f0217a40550c5742b319ed9487.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/c75bdfceb1848065cd544305cb9098163923f09b.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/f1e5d97931f513cb1dbfcbd216068e050b60ceef.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/cfe9a17a1adbfcaad2209c8607e28755e49211fa.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/18faefd85479397acbecb002f1c002d84c2563f7.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/7aa634a8e11e2eec787dab95254c0158659bc23c.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/8cd90110992f710d340bc471cf68cb7a6f974dee.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/56098f3169a43e2369afa8d627b5c8aebcda654f.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/4c8983718b8170c50102e50ae79c781efa5ec6d1.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- INSERT INTO web_photo (accountid,profile_image,cover_image) VALUES (:accountid0,:profile_image0,:cover_image0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","profile_image0":"man-3.png","cover_image0":"388882-PC5X6X-544.jpg"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/d2e987cb1daac4978f7fe24f67ec51a649906de8.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/55d18a54661e1c32ffe961cd17aa9c6c337fb17d.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/34103665398110ce49e813207998245675894d3d.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/737cbb6b59c93cfb31d99208bea0a84994bdb0a7.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/b9b33a25f0d8fca73b71a1cce2017606fe796e3c.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/1bac0e89ee51d0a54d17fc644b154b7a317ce078.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/9fd0a73af3f1ead6dcf2e624d08348caeb4f49e0.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/55ffd39bc757f8399c7c989f8215a696d97378b4.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/fd629f3621eb1f830cda6880eaf73846fa9beac3.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/df19533f3b1a5c44493af41e8280f8c250932c36.png","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/9929b2ce8378e15cf30605e9cba0abe3d190ce84.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET accountid = :accountid , firstname = :firstname , lastname = :lastname , telephone = :telephone , email = :email , address = :address , username = :username , sexid = :sexid , stateid = :stateid , about = :about  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"2","firstname":"Obi","lastname":"Sammy","telephone":"08100001111","email":"admin@wekiwork.com","address":"alagbado lagos state","username":"doctor","sexid":"1","stateid":"1","about":"A certified ","accountid0":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET accountid = :accountid , firstname = :firstname , lastname = :lastname , telephone = :telephone , email = :email , address = :address , username = :username , sexid = :sexid , stateid = :stateid , about = :about  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"2","firstname":"Obi","lastname":"Sammy","telephone":"08100001111","email":"admin@wekiwork.com","address":"alagbado lagos state","username":"doctor","sexid":"1","stateid":"1","about":"A certified neurosurgeon. based in abuja nigeria. You can contact me for health tips, general advise and medical treatment,","accountid0":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/6cd64839392c746659d62615b0d7ecc278799a81.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET accountid = :accountid , firstname = :firstname , lastname = :lastname , telephone = :telephone , email = :email , address = :address , username = :username , sexid = :sexid , stateid = :stateid , about = :about  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"2","firstname":"Obi","lastname":"Sammy","telephone":"08100001111","email":"admin@wekiwork.com","address":"alagbado lagos state","username":"doctor","sexid":"1","stateid":"1","about":"A certified neurosurgeon. based in Abuja Nigeria. You can contact me for health tips, general advise and medical treatment,","accountid0":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/a3c9baa3da99d69adbcf8a3ffadd86e8d0d68d6d.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/cbf812138eb7d44c8b8d381d790bc7a4c143cc56.jpeg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/73d621a23fcb79969640ee6517e6b32b6c59718e.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-My] Dumping data for table `My`
--- SQL ---
--- UPDATE My SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-My]

--- [BEGIN-My] Dumping data for table `My`
--- SQL ---
--- UPDATE My SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-My]

--- [BEGIN-Account] Dumping data for table `Account`
--- SQL ---
--- UPDATE Account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-Account]

--- [BEGIN-Account] Dumping data for table `Account`
--- SQL ---
--- UPDATE Account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-Account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/fc32ea56761a7277f9ec8ed47e42de3fc56b5147.png","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/261975dcc44375c0f11e0ad310f290f4acdd73c1.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/47d4bc17d42892a7501742d8eb9669d30f844622.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/16a1011ec342831f3c9e4ef061e29f204a22cb55.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- DELETE FROM wishlist WHERE addedby = :addedby 
--- ENDSQL ---
--- BINDS ---
--- {"addedby":"1"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-wishlist] Dumping data for table `wishlist`
--- SQL ---
--- INSERT INTO wishlist (addedby,id,type) VALUES (:addedby0,:id0,:type0)
--- ENDSQL ---
--- BINDS ---
--- {"addedby0":"1","id0":"2","type0":"Doctor"}---
--- ENDBINDS ---
--- [END-wishlist]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET accountid = :accountid , firstname = :firstname , lastname = :lastname , telephone = :telephone , email = :email , address = :address , username = :username , sexid = :sexid , stateid = :stateid , about = :about , groups = :groups  WHERE accountid = :accountid0 
--- ENDSQL ---
--- BINDS ---
--- {"accountid":"2","firstname":"Obi","lastname":"Sammy","telephone":"08100001111","email":"admin@wekiwork.com","address":"alagbado lagos state","username":"doctor","sexid":"1","stateid":"1","about":"A certified neurosurgeon. based in Abuja Nigeria. You can contact me for health tips, general advise and medical treatment,","groups":"online,home service","accountid0":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"0","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-account] Dumping data for table `account`
--- SQL ---
--- UPDATE account SET isavaliable = :isavaliable  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"isavaliable":"1","accountid":"2"}---
--- ENDBINDS ---
--- [END-account]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/4c00ee30729b86c2a4286a8c5ddefe9c.png"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/d38a85b535affc0f367c9914ba1fdd27.png"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- DELETE FROM photo_gallery WHERE photoid=:photoid 
--- ENDSQL ---
--- BINDS ---
--- {"photoid":"2"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- DELETE FROM photo_gallery WHERE photoid=:photoid 
--- ENDSQL ---
--- BINDS ---
--- {"photoid":"1"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/7c9c2001e7dd8d2961f0a379595862d4.jpg"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/5e57dbe0f732cb5dcf42b67dbc185633.png"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- DELETE FROM photo_gallery WHERE photoid=:photoid 
--- ENDSQL ---
--- BINDS ---
--- {"photoid":"1"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET profile_image = :profile_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"profile_image":".\/pages\/My\/Uploads\/\/b1ef0ff9a29fa7192d44c1dd675393b39899ed56.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-web_photo] Dumping data for table `web_photo`
--- SQL ---
--- UPDATE web_photo SET cover_image = :cover_image  WHERE accountid=:accountid 
--- ENDSQL ---
--- BINDS ---
--- {"cover_image":".\/pages\/My\/Uploads\/\/a5418bf4f40e94318e9ab7f5d199f402dadbb5e0.jpg","accountid":"2"}---
--- ENDBINDS ---
--- [END-web_photo]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- DELETE FROM photo_gallery WHERE photoid = :photoid 
--- ENDSQL ---
--- BINDS ---
--- {"photoid":"4"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- DELETE FROM photo_gallery WHERE photoid = :photoid 
--- ENDSQL ---
--- BINDS ---
--- {"photoid":"3"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/ccb0a3d5820e38842cee0b72a730fd70.jpg"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/c86819786380a8d02e14b48213d049cf.jpg"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-photo_gallery] Dumping data for table `photo_gallery`
--- SQL ---
--- INSERT INTO photo_gallery (accountid,photo) VALUES (:accountid0,:photo0)
--- ENDSQL ---
--- BINDS ---
--- {"accountid0":"2","photo0":".\/pages\/My\/Uploads\/2a4bfed209013703140d21e128bf0a0e.jpg"}---
--- ENDBINDS ---
--- [END-photo_gallery]

--- [BEGIN-rating] Dumping data for table `rating`
--- SQL ---
--- INSERT INTO rating (ratingcode,optionid,accountid,ratingfrom) VALUES (:ratingcode0,:optionid0,:accountid0,:ratingfrom0)
--- ENDSQL ---
--- BINDS ---
--- {"ratingcode0":"1157454441614110","optionid0":"1","accountid0":"2","ratingfrom0":"1"}---
--- ENDBINDS ---
--- [END-rating]

--- [BEGIN-rating] Dumping data for table `rating`
--- SQL ---
--- INSERT INTO rating (ratingcode,optionid,accountid,ratingfrom) VALUES (:ratingcode0,:optionid0,:accountid0,:ratingfrom0)
--- ENDSQL ---
--- BINDS ---
--- {"ratingcode0":"1157454464424987","optionid0":"1","accountid0":"2","ratingfrom0":"1"}---
--- ENDBINDS ---
--- [END-rating]

--- [BEGIN-rating] Dumping data for table `rating`
--- SQL ---
--- INSERT INTO rating (ratingcode,optionid,accountid,ratingfrom) VALUES (:ratingcode0,:optionid0,:accountid0,:ratingfrom0)
--- ENDSQL ---
--- BINDS ---
--- {"ratingcode0":"1157454489921363","optionid0":"1","accountid0":"2","ratingfrom0":"1"}---
--- ENDBINDS ---
--- [END-rating]

--- [BEGIN-rating] Dumping data for table `rating`
--- SQL ---
--- INSERT INTO rating (ratingcode,optionid,accountid,ratingfrom) VALUES (:ratingcode0,:optionid0,:accountid0,:ratingfrom0)
--- ENDSQL ---
--- BINDS ---
--- {"ratingcode0":"1157454833115376","optionid0":"1","accountid0":"2","ratingfrom0":"1"}---
--- ENDBINDS ---
--- [END-rating]

CREATE TABLE IF NOT EXISTS `cart` (
	cartid BIGINT(20) auto_increment primary key, 
	pharmacyid BIGINT , 
	accountid BIGINT , 
	drugid BIGINT , 
	quantity INT , 
	orderNote TEXT , 
	amount DOUBLE , 
	dateRequested DATETIME default CURRENT_TIMESTAMP, 
	dateDelivered VARCHAR(255)
);
ALTER TABLE `cart` CHANGE COLUMN orderNote txref VARCHAR(255);
CREATE TABLE IF NOT EXISTS `cart` (
	cartid BIGINT(20) auto_increment primary key, 
	pharmacyid BIGINT , 
	accountid BIGINT , 
	drugid BIGINT , 
	quantity INT , 
	txref VARCHAR(255) , 
	amount DOUBLE , 
	dateRequested DATETIME default CURRENT_TIMESTAMP, 
	dateDelivered VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS `cartOrderDetails` (
	cartOrderDetailid BIGINT(20) auto_increment primary key, 
	txref VARCHAR(255) , 
	orderDetails TEXT
);
ALTER TABLE `cart` CHANGE COLUMN amount shipping VARCHAR(255);
ALTER TABLE `cart` CHANGE COLUMN dateRequested amount DOUBLE;
ALTER TABLE `cart` CHANGE COLUMN dateDelivered dateRequested DATETIME default CURRENT_TIMESTAMP;
ALTER TABLE `cart` ADD dateDelivered VARCHAR(255) AFTER dateRequested;
CREATE TABLE IF NOT EXISTS `cart` (
	cartid BIGINT(20) auto_increment primary key, 
	pharmacyid BIGINT , 
	accountid BIGINT , 
	drugid BIGINT , 
	quantity INT , 
	txref VARCHAR(255) , 
	shipping VARCHAR(255) , 
	amount DOUBLE , 
	dateRequested DATETIME default CURRENT_TIMESTAMP, 
	dateDelivered VARCHAR(255)
);
CREATE TABLE IF NOT EXISTS `notification` (
	notificationid BIGINT(20) auto_increment primary key, 
	target VARCHAR(255) , 
	accountid VARCHAR(255) , 
	hash VARCHAR(255) , 
	seen INT default 0
);