<?php

function encrypt($string) {
  $var = base64_encode($string);
  $var = str_replace("=", "_", $var);
  $var = str_replace("+", "-", $var);
  $var = str_replace("1", "!", $var);
  $var = str_replace("0", "1", $var);
  $var = str_replace("!", "0", $var);
  $var = str_replace("A", "@@", $var);
  $var = str_replace("a", "@", $var);
  $var = str_replace("e", "x", $var);
  $var = str_replace("f", "e", $var);
  $var = str_replace("x", "f", $var);
  $enc = bin2hex($var);
  return $enc;
}

function decrypt($encrypted) {
  $var = hex2bin($encrypted);
  $var = str_replace("_", "=", $var);
  $var = str_replace("-", "+", $var);
  $var = str_replace("1", "!", $var);
  $var = str_replace("0", "1", $var);
  $var = str_replace("!", "0", $var);
  $var = str_replace("@@", "A", $var);
  $var = str_replace("@", "a", $var);
  $var = str_replace("f", "x", $var);
  $var = str_replace("e", "f", $var);
  $var = str_replace("x", "e", $var);
  $dex = base64_decode($var);
  return $dex;
}

function encode($id) {
  $id_str = (string) $id;
  $offset = rand(0, 9);
  $encoded = chr(79 + $offset);
  $len = strlen($id_str);
  for ($i = 0; $i < $len; ++$i) {
    $encoded .= chr(65 + $id_str[$i] + $offset);
  }
  return $encoded;
}

function decode($encoded) {
  $offset = ord($encoded[0]) - 79;
  $encoded = substr($encoded, 1);
  $len = strlen($encoded);
  for ($i = 0; $i < $len; ++$i) {
    $encoded[$i] = ord($encoded[$i]) - $offset - 65;
  }
  return (int) $encoded;
}

function get_status($int) {
  if ($int==0 || $int=='0') {
    return 'Waiting For Payment';
  } elseif ($int==1 || $int=='1') {
    return 'Paid';
  } else {
    return 'Failed';
  }
}

function getStatus($int) {
  if ($int==0 || $int=='0') {
    return '<div class="badge badge-info">Waiting For Payment</div>';
  } elseif ($int==1 || $int=='1') {
    return '<div class="badge badge-success">Paid</div>';
  } else {
    return '<div class="badge badge-danger">Failed</div>';
  }
}

function datex_format($date, $format) {
  return date($format, strtotime($date));
}

function exec_curl($url, $post=false, $postFields=false, $arrayHeader=false) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  if ($post) {
    curl_setopt($ch, CURLOPT_POST, 1);
  }
  if ($postFields) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
  }
  if ($arrayHeader) {
    curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
  }
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

  $server_output = curl_exec($ch);
  curl_close ($ch);

  return $server_output;
}

function xendit_curl($url, $data=[], $secretKey='', $isPOst=false) {
  $curl = curl_init();
  $headers = [];
  $headers[] = 'Authorization: Basic ' . $secretKey;
  $headers[] = 'Content-Type: application/json';

  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  // curl_setopt($curl, CURLOPT_USERPWD, $secret_key.":");

  // curl_setopt($curl, CURLOPT_VERBOSE, 1);
  // curl_setopt($curl, CURLOPT_HEADER, 1);
  curl_setopt($curl, CURLOPT_URL, $url);
  if ($isPOst) {
    $payload = json_encode($data);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
  }
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($curl);
  // $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
  // $header = substr($result, 0, $header_size);
  // $body = substr($result, $header_size);

  curl_close ($curl);

  return $result;
}

function xendit_disbursement_curl($url, $data=[], $secretKey='', $isPOst=false) {
  $curl = curl_init();
  $headers = [];
  $headers[] = 'Authorization: Basic ' . $secretKey;
  if ($isPOst) {
    $headers[] = 'X-IDEMPOTENCY-KEY: ' . $data['external_id'];
    $headers[] = 'Content-Type: application/json';
  }

  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($curl, CURLOPT_URL, $url);
  if ($isPOst) {
    $payload = json_encode($data);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
  }
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($curl);
  curl_close ($curl);

  return $result;
}

function create_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function jin_date_ina($date_sql, $tipe = 'full', $time = false) {
  $date = '';
  if($tipe == 'full') {
    $nama_bulan = array(1=>"Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
  } else {
    $nama_bulan = array(1=>"Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
  }
  if($time) {
    $exp = explode(' ', $date_sql);
    $exp = explode('-', $exp[0]);
    if(count($exp) == 3) {
      $bln = $exp[1] * 1;
      $date = $exp[2].' '.$nama_bulan[$bln].' '.$exp[0];
    }   
    $exp_time = $exp = explode(' ', $date_sql);
    $date .= ' jam ' . substr($exp_time[1], 0, 5);
  } else {
    $exp = explode('-', $date_sql);
    if(count($exp) == 3) {
      $bln = $exp[1] * 1;
      if($bln > 0) {
        $date = $exp[2].' '.$nama_bulan[$bln].' '.$exp[0];
      }
    }
  }
  return $date;
}

function jin_nama_bulan($bln, $tipe='full') {
  $bln = $bln * 1;
  if($tipe == 'full') {
    $nama_bulan = array(1=>"Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
  } else {
    $nama_bulan = array(1=>"Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des");
  }
  return $nama_bulan[$bln];
}