async function doQuery(DB,type,params=[]){
    var promise = false
    switch(type){
        case 'getFlatsLSandCRMid':
            promise = new Promise((resolve,reject)=>{
                DB.query(`SELECT flatsid, cf_1420 AS ls, cf_1235 AS owner
								FROM vtiger_flatscf AS FCF
								INNER JOIN vtiger_crmentity AS CRM ON CRM.crmid = FCF.flatsid
								WHERE CRM.deleted = 0`,(err,res)=>{
                        if(err) reject(err)
                        else if(res.length!=0)
                            resolve(new Map(res.map(row => [row.ls, {crmid : row.flatsid, owner:row.owner}])))
                        else resolve(false)
                    })
            })	
            break;
        case 'getExistPayments':
            promise = new Promise((resolve,reject)=>{
                DB.query(`SELECT CONCAT(pay_date) AS pay_date, ROUND(amount,3) AS amount, cf_1420 AS ls, doc_no
                            FROM sp_payments AS P
                            INNER JOIN sp_paymentscf AS PCF ON PCF.payid = P.payid
                            INNER JOIN vtiger_crmentity AS CRM ON CRM.crmid = P.payid 
                            INNER JOIN vtiger_flatscf AS F ON F.flatsid = PCF.cf_1416
                            WHERE cf_1295 = ? AND CRM.deleted = 0 AND type_payment = 'Cashless Transfer' AND TRIM(cf_1420) > 0`,[params],(err,res)=>{
                        if(err) reject(err)
                        else if(res.length!=0){
                            var result = res.reduce((r, a) => {
                              r[a.ls] = r[a.ls] || [];
                              r[a.ls].push({pay_date:a.pay_date, amount: parseFloat(a.amount), doc_no:a.doc_no});
                              return r;
                            }, {});
                            var payments = new Map()
                            for(var i in result) payments.set(Number(i),result[i])
                            resolve(payments)
                        }
                        else resolve(new Map())
                    })
            })    
            break;
        case 'searchContact':
            promise = new Promise((resolve,reject)=>{
                DB.query(`SELECT CD.contactid, CD.lastname FROM vtiger_contactdetails AS CD
                        INNER JOIN vtiger_crmentity AS CRM ON CRM.crmid = CD.contactid
                        WHERE CD.lastname LIKE CONCAT('%',?,'%') AND deleted = 0
                        LIMIT 5`,[params],(err,res)=>{
                        if(err) resolve(false)
                        else if(res.length!=0){
                            var ans = []
                            for(var row of res)
                                ans.push({crmid:row.contactid, name: row.lastname})
                            resolve(ans)
                        }
                        else resolve(false)
                    })
            })    
            break;
        default: return await promise;
    }
    return await promise;
}
module.exports = {
    doQuery
}