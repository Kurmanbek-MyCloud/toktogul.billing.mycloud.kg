<?php
$key = md5($_POST['name']."".$_POST['surname']."".$_POST['pbxmanagerid']);
if($_POST['key'] == $key)
{
    try{
        $host = 'localhost';
        $db   = 'prefect_vtiger';
        $user = 'vtiger_admin';
        $pass = 'svviiJx66Bw=';
        $charset = 'utf8';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $opt);
        if($_POST['type'] == "Contacts")
        {
            $stmt = $pdo->prepare("SELECT max(crmid+1) as max, a.crmid as crmid, b.user as user, b.customernumber, (SELECT contact_no from vtiger_contactdetails ORDER BY contactid DESC LIMIT 1) AS contact_no from vtiger_crmentity AS a INNER JOIN vtiger_pbxmanager AS b ON a.crmid = b.pbxmanagerid");
            $stmt->execute();
            $result = $stmt->fetchAll();
            $id = explode("КОНТАКТ_",$result[0]['contact_no']);
            $contact_no = "КОНТАКТ_".intval($id[1]+1);    
            $sql = "INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid,modifiedby,setype,createdtime,modifiedtime,source,label) VALUES (?,?,?,?,?,?,?,?,?)";
            $sql2 = "UPDATE vtiger_crmentity_seq set id=?";
            $sql3 = "INSERT INTO vtiger_contactdetails (contactid,contact_no,accountid,firstname,lastname,email,phone,isconvertedfromlead) VALUES (?,?,?,?,?,?,?,?)";
            $sql4 = "UPDATE vtiger_pbxmanager set customer=?,customertype=? where pbxmanagerid = ?";
            $sql5 = "INSERT INTO vtiger_contactscf (contactid) values (?)";
            $pdo->prepare($sql)->execute([$result[0]['max'], $result[0]['user'],$result[0]['user'],$result[0]['user'],$_POST['type'],date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),'CRM',"создан из карточки звонка"]);
            $pdo->prepare($sql2)->execute([$result[0]['max']]);
            $pdo->prepare($sql3)->execute([$result[0]['max'],$contact_no,0,$_POST['name'],$_POST['surname'],$_POST['email'],$result[0]['customernumber'],0]);
            $pdo->prepare($sql4)->execute([$result[0]['max'],$_POST['type'],$_POST['pbxmanagerid']]);
            $pdo->prepare($sql5)->execute([$result[0]['max']]);
        }
        elseif($_POST['type'] == "Leads")
        {
            $stmt = $pdo->prepare("SELECT max(crmid+1) as max, a.crmid as crmid, b.user as user, b.customernumber, (SELECT lead_no from vtiger_leaddetails ORDER BY leadid DESC LIMIT 1) AS lead_no from vtiger_crmentity AS a INNER JOIN vtiger_pbxmanager AS b ON a.crmid = b.pbxmanagerid");
            $stmt->execute();
            $result = $stmt->fetchAll(); 
            $id = explode("КОНТАКТ_",$result[0]['lead_no']);    
            $lead_no = "ОБР_".intval($id[1]+1);
            $sql = "INSERT INTO vtiger_crmentity (crmid, smcreatorid, smownerid,modifiedby,setype,createdtime,modifiedtime,source,label) VALUES (?,?,?,?,?,?,?,?,?)";
            $sql2 = "UPDATE vtiger_crmentity_seq set id=?";
            $sql3 = "INSERT INTO vtiger_leaddetails (leadid,lead_no,email,firstname,lastname) VALUES (?,?,?,?,?)";
            $sql4 = "UPDATE vtiger_pbxmanager set customer=?,customertype=? where pbxmanagerid = ?";
            $sql5 = "INSERT INTO vtiger_leadscf (leadid) values (?)";
            $sql6 = "INSERT INTO vtiger_leadaddress (leadaddressid,phone,leadaddresstype) values (?,?,?)";
            $pdo->prepare($sql)->execute([$result[0]['max'], $result[0]['user'],$result[0]['user'],$result[0]['user'],$_POST['type'],date("Y-m-d H:i:s"),date("Y-m-d H:i:s"),'CRM',"создан из карточки звонка"]);
            $pdo->prepare($sql2)->execute([$result[0]['max']]);
            $pdo->prepare($sql3)->execute([$result[0]['max'],$lead_no,$_POST['email'],$_POST['name'],$_POST['surname']]);
            $pdo->prepare($sql4)->execute([$result[0]['max'],$_POST['type'],$_POST['pbxmanagerid']]);
            $pdo->prepare($sql5)->execute([$result[0]['max']]);
            $pdo->prepare($sql6)->execute([$result[0]['max'],$result[0]['phone'],'Billing']);
            $pdo->prepare("INSERT INTO vtiger_leadsubdetails (leadsubscriptionid,website,callornot,readornot,empct) VALUES (?,?,?,?,?)")->execute([$result[0]['max'],'','0','0','0']);
        }
    }
    catch(Exception $e)
    {
        echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
    }
}
else
{
    echo "Как вы тут оказались?";
}
?>