<?php

function App() {
    $app['name'] = 'PACARAN';
    $app['rekening'] = 'DGPAY PACARAN';
    $app['secretKey'] = base64_encode('xnd_production_JT52cj42lnPQUXwvyWDPdbe0XEH1zPInSD4TgPtyRiTVh0LAj2Qy4xRentPuj:');

    return $app;
}

function smtp() {
    $smtp['Ssl'] = 'ssl';
    $smtp['Host'] = 'indobeneficiadigital.com';
    $smtp['Port'] = 465;
    $smtp['Username'] = 'mailer@indobeneficiadigital.com';
    $smtp['Password'] = 'Password123.';
    $smtp['MailFrom'] = 'mailer@indobeneficiadigital.com';
    $smtp['NameFrom'] = 'System Mailer';

    return $smtp;
}