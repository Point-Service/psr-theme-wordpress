# SMTP dedicato agli OTP (PSR)
Centro Sicurezza → OTP email → SMTP dedicato agli OTP.
Solo ID 1, sessione verificata, HTTPS. Parametri fissi: otp@servizipa.cloud,
out.postassl.it:465, TLS con verifica certificato. Nessuna password in GitHub.
Per ogni Comune: inserire password, salvare con check disattivato, inviare prova
al destinatario OTP ID 1 e verificare la ricezione. Dopo almeno 30 secondi
abilitare il check e salvare: ulteriore test SMTP, nessuna attivazione se fallisce.
Provare un login da un altro browser prima di uscire dalla sessione corrente.
Il server SMTP può accettare un messaggio che poi non viene consegnato.
Il flag disattivato usa il percorso wp_mail preesistente; nessun fallback
automatico con flag attivo. Ripristino da Plesk File Manager:
define('PSR_OTP_SMTP_ENABLED', false); in wp-config.php prima del bootstrap.
Cifratura XChaCha20-Poly1305, HKDF SHA256 sulle quattro chiavi esplicite di
wp-config.php, nonce casuale, opzione non autoload. Rotazione delle chiavi
WordPress richiede reinserimento password. Protegge dalla sola copia del DB,
non da compromissione PHP/server o di database insieme al wp-config.
Le credenziali condivise tra Comuni comportano rischio comune: ruotare in caso
di incidente. Non usare la password precedentemente condivisa in chat.
Non sono modificati destinatari OTP, verifiche, recupero password e altre email.
