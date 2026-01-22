const config = require('./config.json')
const express = require('express')
const multer  = require('multer')
const moment  = require('moment')
const mysql  = require('mysql2')
// const CRM = require("./crm")
const DB = require("./db")
const XLSX = require('xlsx')

const app = express()

const storageConfig = multer.diskStorage({
    destination: (request, file, cb) =>{ cb(null, "uploads/") },
    filename: (request, file, cb) =>{ cb(null,String(request.body.paySystem).replace('/','')+moment().format('DD-MM-YYYY HH:mm:ss')+'.'+String(file.originalname).split('.').pop()) }
});
const fileFilter = (request, file, cb) => {
    // console.log(file.mimetype)
    if(file.mimetype === "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" || file.mimetype === 'application/vnd.ms-excel') cb(null, true);
    else cb(null, false);
}

var sessionName, DB_Connection

app.use(express.json()); 
app.use(express.urlencoded({ extended: true })); 
app.use(express.static(__dirname));
app.use(multer({storage:storageConfig, fileFilter: fileFilter,limits: { fieldSize: 10000 * 1024 * 1024 }}).any('file','invoice_file'));

app.listen(3033,async function(){
    console.clear()
    try{
        DB_Connection = mysql.createPool({
            connectionLimit: 5,
            host: config.db_host,
            user: config.db_user,
            database: config.db_name,
            password: config.db_pass
        })
        console.log('server started ::: '+moment().format('DD-MM-YYYY HH:mm:ss'))
    }
    catch(e){
        console.error("Запуск не удался",e)
        process.exit(e)
    }
})

// app.post('/uploadInvoice',async function(request, response){
//     try{
//         console.log(`Запрос на создание платежей (${request.body.paySystem}) ::: `+moment().format('DD-MM-YYYY HH:mm:ss'))
//         if(!request.files || request.files.length == 0) throw new Error('Ошибка загрузки файла')
//         sessionName = await CRM.login()
//         var flats = await DB.doQuery(DB_Connection,'getFlatsLSandCRMid')
//         var paySystem = request.body.paySystem
//         var sheetname = false
//         var workbook = XLSX.readFile('uploads/'+request.files[0].filename);
//         var created = 0, errors = 0, notFindedLS = 0, exist = 0
//         for (var sheet in workbook.Sheets){ sheetname = sheet }
//         try{
//             var tableData = XLSX.utils.sheet_to_json(workbook.Sheets[sheetname],{ header: 1 });    
//             if(tableData.length == 0) throw new Error('Ошибка парсинга файла')
//             else {
//                 switch (paySystem){
//                     case 'МегаПэй':
//                         var payments = await DB.doQuery(DB_Connection,'getExistPayments',paySystem)
//                         for (var i = 1; i < tableData.length-2; i++) {
//                             try{
//                                 var ls = Number(tableData[i][1])
//                                 var pay_date = moment(tableData[i][0]).format('YYYY-MM-DD')
//                                 var flat = flats.get(ls) //ЛС
//                                 if (flat){
//                                     if (payments.get(ls)?.find(row=>row.amount == parseFloat(tableData[i][2]) && row.pay_date == pay_date))
//                                         exist++
//                                     else{
//                                         var data = {
//                                             pay_date: pay_date, //Дата платежа
//                                             payer: '12x'+flat.owner,
//                                             pay_type: 'Receipt',
//                                             type_payment: "Cashless Transfer",
//                                             assigned_user_id:'19x1',
//                                             amount: parseFloat(tableData[i][2]),
//                                             cf_1295: paySystem,
//                                             spstatus: "Executed",
//                                             cf_1416: '46x'+flat.crmid
//                                         }
//                                         if (await CRM.APIcreate(sessionName,'SPPayments',data)) created++
//                                         else errors++
//                                     }
//                                 }
//                                 else notFindedLS++
//                             }
//                             catch(e){
//                                 errors++
//                             }
//                         }
//                         break
//                     case 'Finca':
//                         var payments = await DB.doQuery(DB_Connection,'getExistPayments',paySystem)
//                         for (var i = 1; i < tableData.length-1; i++) {
//                             try{
//                                 var ls = Number(tableData[i][4])
//                                 var pay_date = moment(ExcelDateToJSDate(tableData[i][1])).format('YYYY-MM-DD')
//                                 var flat = flats.get(ls) //ЛС
//                                 if (flat){
//                                     if (payments.get(ls)?.find(row=>row.doc_no == String(tableData[i][0]) && row.pay_date == pay_date))
//                                         exist++
//                                     else{
//                                         var data = {
//                                             pay_date: pay_date, //Дата платежа
//                                             payer: '12x'+flat.owner,
//                                             pay_type: 'Receipt',
//                                             type_payment: "Cashless Transfer",
//                                             assigned_user_id:'19x1',
//                                             amount: parseFloat(tableData[i][10]),
//                                             cf_1295: paySystem,
//                                             spstatus: "Executed",
//                                             cf_1416: '46x'+flat.crmid,
//                                             doc_no: tableData[i][0]
//                                         }
//                                         if (await CRM.APIcreate(sessionName,'SPPayments',data)) created++
//                                         else errors++
//                                     }
//                                 }
//                                 else notFindedLS++
//                             }
//                             catch(e){
//                                 errors++
//                             }
//                         }
//                         break
//                     case 'O!-Деньги':
//                         var payments = await DB.doQuery(DB_Connection,'getExistPayments',paySystem)
//                         for (var i = 1; i < tableData.length-2; i++) {
//                             try{
//                                 var ls = Number(tableData[i][1])
//                                 var pay_date = moment(tableData[i][2], 'DD.MM.YYYY HH:mm:ss').format('YYYY-MM-DD')
//                                 var flat = flats.get(ls) //ЛС
//                                 if (flat){
//                                     if (payments.get(ls)?.find(row=>row.doc_no == String(tableData[i][4]) && row.pay_date == pay_date))
//                                         exist++
//                                     else{
//                                         var data = {
//                                             pay_date: pay_date, //Дата платежа
//                                             payer: '12x'+flat.owner,
//                                             pay_type: 'Receipt',
//                                             type_payment: "Cashless Transfer",
//                                             assigned_user_id:'19x1',
//                                             amount: parseFloat(tableData[i][3]),
//                                             cf_1295: paySystem,
//                                             spstatus: "Executed",
//                                             cf_1416: '46x'+flat.crmid,
//                                             doc_no: String(tableData[i][4])
//                                         }
//                                         if (await CRM.APIcreate(sessionName,'SPPayments',data)) created++
//                                         else errors++
//                                     }
                                    
//                                 }
//                                 else notFindedLS++
//                             }
//                             catch(e){
//                                 console.error(e)
//                                 errors++
//                             }
                            
//                         }
//                         break
//                     case 'Alymbek GSM':
//                         var payments = await DB.doQuery(DB_Connection,'getExistPayments',paySystem)
//                         for (var i = 3; i < tableData.length-1; i++) {
//                             if (tableData[i].length!=7) break;
//                             try{
//                                 var ls = Number(tableData[i][1])
//                                 var pay_date = moment(tableData[i][2], 'DD.MM.YYYY HH:mm:ss').format('YYYY-MM-DD')
//                                 var flat = flats.get(ls) //ЛС
//                                 if (flat){
//                                     if (payments.get(ls)?.find(row=>row.doc_no == String(tableData[i][4])))
//                                         exist++
//                                     else{
//                                         var data = {
//                                             pay_date: pay_date, //Дата платежа
//                                             payer: '12x'+flat.owner,
//                                             pay_type: 'Receipt',
//                                             type_payment: "Cashless Transfer",
//                                             assigned_user_id:'19x1',
//                                             amount: parseFloat(tableData[i][3]),
//                                             cf_1295: paySystem,
//                                             spstatus: "Executed",
//                                             cf_1416: '46x'+flat.crmid,
//                                             doc_no: String(tableData[i][4])
//                                         }
//                                         if (await CRM.APIcreate(sessionName,'SPPayments',data)) created++
//                                         else errors++
//                                     }
                                    
//                                 }
//                                 else notFindedLS++
//                             }
//                             catch(e){
//                                 console.error(e)
//                                 errors++
//                             } 
//                         }
//                         break
                    
//                     // !!!!!!!!!!!!!!!!ПРи добавлении новой платежной системы проверять tableData, проврнка типа переменной суммы платежа 
//                     default:
//                         console.error(paySystem)
//                         throw new Error('Неизвестная платежная система')
//                         break                
//                 }
//             }
//         }
//         catch(e){
//             console.error(e)
//             throw new Error(e.message)
//         }
//         response.send({success:true,created:created, errors:errors, notFindedLS:notFindedLS, exist:exist})
//     }
//     catch(e){
//         console.error(moment().format('DD-MM-YYYY HH:mm:ss'),e)
//         response.send({success:false, message:e.message})
//     }
// })

app.post('/searchContact',async function(request, response){
    try{
        var input = request.body.input
        response.send({success:true, rows:await DB.doQuery(DB_Connection,'searchContact',input)})
    }
    catch(e){
        response.send({success:false})    
    }
})

// function ExcelDateToJSDate(serial) {
//    var utc_days  = Math.floor(serial - 25569);
//    var utc_value = utc_days * 86400;                                        
//    var date_info = new Date(utc_value * 1000);

//    var fractional_day = serial - Math.floor(serial) + 0.0000001;

//    var total_seconds = Math.floor(86400 * fractional_day);

//    var seconds = total_seconds % 60;

//    total_seconds -= seconds;

//    var hours = Math.floor(total_seconds / (60 * 60))+6;
//    var minutes = Math.floor(total_seconds / 60) % 60;

//    return new Date(date_info.getFullYear(), date_info.getMonth(), date_info.getDate(), hours, minutes, seconds);
// }