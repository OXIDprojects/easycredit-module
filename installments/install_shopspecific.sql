-- OXPS Easy Credit shop specific module install SQL file --
/**
 * DO NOT RUN THIS SQL MANUALLY!
 * IT IS INTENDED FOR onActivate()
 */

REPLACE INTO `oxobject2group` (`OXID`, `OXSHOPID`, `OXOBJECTID`, `OXGROUPSID`, `OXTIMESTAMP`) VALUES
  ('easycredit1-#shop#', '#shop#', 'easycreditinstallment', 'oxidblacklist', '2017-11-08 11:48:51'),
  ('easycredit2-#shop#', '#shop#', 'easycreditinstallment', 'oxidsmallcust', '2017-11-08 11:48:51'),
  ('easycredit3-#shop#', '#shop#', 'easycreditinstallment', 'oxidmiddlecust', '2017-11-08 11:48:51'),
  ('easycredit4-#shop#', '#shop#', 'easycreditinstallment', 'oxidgoodcust', '2017-11-08 11:48:51'),
  ('easycredit5-#shop#', '#shop#', 'easycreditinstallment', 'oxidforeigncustomer', '2017-11-08 11:48:51'),
  ('easycredit6-#shop#', '#shop#', 'easycreditinstallment', 'oxidnewcustomer', '2017-11-08 11:48:51'),
  ('easycredit7-#shop#', '#shop#', 'easycreditinstallment', 'oxidpowershopper', '2017-11-08 11:48:51'),
  ('easycredit8-#shop#', '#shop#', 'easycreditinstallment', 'oxiddealer', '2017-11-08 11:48:51'),
  ('easycredit9-#shop#', '#shop#', 'easycreditinstallment', 'oxidnewsletter', '2017-11-08 11:48:51'),
  ('easycredit10-#shop#', '#shop#', 'easycreditinstallment', 'oxidadmin', '2017-11-08 11:48:51'),
  ('easycredit11-#shop#', '#shop#', 'easycreditinstallment', 'oxidpriceb', '2017-11-08 11:48:51'),
  ('easycredit12-#shop#', '#shop#', 'easycreditinstallment', 'oxidpricea', '2017-11-08 11:48:51'),
  ('easycredit13-#shop#', '#shop#', 'easycreditinstallment', 'oxidpricec', '2017-11-08 11:48:51'),
  ('easycredit14-#shop#', '#shop#', 'easycreditinstallment', 'oxidnotyetordered', '2017-11-08 11:48:51'),
  ('easycredit14-#shop#', '#shop#', 'easycreditinstallment', 'oxidcustomer', '2017-11-08 11:48:51');