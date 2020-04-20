CREATE VIEW v_mutasi_saldo as
SELECT 
	id_user as id_user,
	tipe as tipe,
	saldo as nominal,
	'Transaksi Saldo' as metode,
	waktu as waktu,
    keterangan as keterangan
FROM transaction_saldo
UNION ALL
SELECT 
	id_user as id_user,
	'Kredit' as tipe,
	nominal as nominal,
	bank_code as metode,
	created_date as waktu,
    'Isi Saldo' as keterangan
FROM transaction_isi_saldo
WHERE status = 'PAID'
UNION ALL
SELECT 
	id_user as id_user,
	'Debit' as tipe,
	amount as nominal,
	bank_code as metode,
	updated_date as waktu,
    CONCAT('Tarik Saldo ke ',account_holder_name,' (',bank_code,')') as keterangan
FROM transaction_tarik_saldo
WHERE status = 'COMPLETED'
UNION ALL
SELECT 
	id_user_penerima as id_user,
	'Kredit' as tipe,
	saldo as nominal,
	'Transfer Saldo' as metode,
	waktu as waktu,
    keterangan as keterangan
FROM transaction_transfer_saldo
WHERE waktu <> ''
UNION ALL
SELECT 
	id_user_pengirim as id_user,
	'Debit' as tipe,
	saldo as nominal,
	'Transfer Saldo' as metode,
	waktu as waktu,
    keterangan as keterangan
FROM transaction_transfer_saldo
WHERE waktu <> ''

ORDER BY id_user ASC, waktu DESC