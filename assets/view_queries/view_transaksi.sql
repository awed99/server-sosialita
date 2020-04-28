CREATE VIEW v_mutasi_transaksi as
SELECT 
	id_user as id_user,
	'Kredit' as tipe,
	id_transaction_isi_saldo as id_transaction,
	'transaction_isi_saldo' as tabel,
    status as status,
	nominal as nominal,
	biaya as biaya,
	total as total,
	bank_code as metode,
	created_date as waktu,
    'Isi Saldo' as keterangan
FROM transaction_isi_saldo
UNION ALL
SELECT 
	id_user as id_user,
	'Debit' as tipe,
	id_transaction_tarik_saldo as id_transaction,
	'transaction_tarik_saldo' as tabel,
    status as status,
	amount as nominal,
	biaya as biaya,
	total as total,
	bank_code as metode,
	updated_date as waktu,
    CONCAT('Tarik Saldo ke ',account_holder_name,' (',bank_code,')') as keterangan
FROM transaction_tarik_saldo

ORDER BY id_user ASC, waktu DESC