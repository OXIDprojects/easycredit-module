-- OXPS Easy Credit module install SQL file --
REPLACE INTO `oxpayments` (`OXID`, `OXACTIVE`, `OXDESC`, `OXADDSUM`, `OXADDSUMTYPE`, `OXADDSUMRULES`, `OXFROMBONI`, `OXFROMAMOUNT`, `OXTOAMOUNT`, `OXVALDESC`, `OXCHECKED`, `OXDESC_1`, `OXVALDESC_1`, `OXDESC_2`, `OXVALDESC_2`, `OXDESC_3`, `OXVALDESC_3`, `OXLONGDESC`, `OXLONGDESC_1`, `OXLONGDESC_2`, `OXLONGDESC_3`, `OXSORT`) VALUES ('easycreditinstallment',	1,	'ratenkauf by easyCredit',	0,	'abs',	0,	0,	200,	10000,	'',	0,	'ratenkauf by easyCredit', '',	'',	'',	'',	'',	'','',	'',	'',	0);

REPLACE INTO `oxobject2payment` (`OXID`, `OXPAYMENTID`, `OXOBJECTID`, `OXTYPE`) VALUES ('ec2oxstandard',	'easycreditinstallment',	'oxidstandard',	'oxdelset');