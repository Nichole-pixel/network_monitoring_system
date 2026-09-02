<?php
session_start();
include('../db.php');

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$toast = '';

if (isset($_GET['added'])) $toast = 'added';
elseif (isset($_GET['deleted'])) $toast = 'deleted';
elseif (isset($_GET['updated'])) $toast = 'updated';

if ($search !== "") {
    $stmt = $conn->prepare("SELECT * FROM client WHERE client_id LIKE ? OR pc_no LIKE ? OR mac_address LIKE ?");
    $param = "%$search%";
    $stmt->bind_param("sss", $param, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM client");
}

}

?>

<!DOCTYPE html>
<html>
<head>
<title>Client Dashboard</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding:40px;
    background:url('../images/background.jpg') no-repeat center/cover fixed;
    position:relative;
}
body::before{
    content:'';
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
}

/* CONTAINER */
.container{
    width:95%;
    max-width:1200px;
    padding:25px;
    border-radius:18px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    position:relative;
    z-index:1;
}

h2{color:#fff;text-align:center;margin-bottom:20px;}

.top-bar{
    display:flex;
    gap:10px;
    margin-bottom:15px;
}

/* INPUT */
input{
    flex:1;
    padding:10px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.2);
    background:rgba(0,0,0,0.3);
    color:#fff;
}

/* BUTTON */
.btn{
    padding:9px 14px;
    border-radius:10px;
    text-decoration:none;
    font-size:13px;
    color:#fff;
    border:1px solid rgba(255,255,255,0.2);
    background:rgba(255,255,255,0.12);
    transition:.3s;
}
.btn:hover{transform:scale(1.05);background:rgba(255,255,255,0.25);}

.add{border-color:rgba(0,255,120,.6);box-shadow:0 0 12px rgba(0,255,120,.3);}
.edit{border-color:rgba(0,150,255,.6);box-shadow:0 0 12px rgba(0,150,255,.3);}
.delete{border-color:rgba(255,80,80,.6);box-shadow:0 0 12px rgba(255,80,80,.3);}

/* TABLE */
table{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;}
th{background:rgba(255,255,255,0.2);color:#fff;padding:12px;}
td{padding:12px;color:#fff;text-align:center;border-bottom:1px solid rgba(255,255,255,0.1);}

/* STATUS */
.online{color:#00ff90;font-weight:bold;}
.offline{color:#ff5252;font-weight:bold;}

/* MODAL */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    backdrop-filter:blur(6px);
    justify-content:center;
    align-items:center;
}
.modal-content{
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(16px);
    padding:25px;
    border-radius:12px;
    text-align:center;
    color:#fff;
}

/* TOAST */
.toast{
    position:fixed;
    bottom:25px;
    right:25px;
    padding:14px;
    border-radius:10px;
    color:#fff;
    opacity:0;
    transform:translateY(20px);
    transition:.4s;
}
.toast.show{opacity:1;transform:translateY(0);}
.toast.success{
    background:rgba(0,255,120,0.2);
    border:1px solid rgba(0,255,120,0.6);
}
</style>
</head>

<body>

<div class="container">
    <h2>Client Dashboard</h2>

    <div class="top-bar">
        <a href="../welcome_dashboard.php" class="btn">⬅ Back</a>
        <input type="text" id="searchInput" placeholder="Search..." value="<?= e($search) ?>">
        <a href="add_client.php" class="btn add">+ Add Client</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>PC No</th>
                <th>MAC Address</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php while($row = $result->fetch_assoc()): 
            $is_online = false;
            if (!empty($row['last_seen'])) {
                $last_seen_time = strtotime($row['last_seen']);
                $current_time = time();
                // If the agent checked in within the last 30 seconds, it is online
                if (($current_time - $last_seen_time) <= 30) {
                    $is_online = true;
                }
            }
        ?>
        <tr>
            <td><?= e($row['pc_no']) ?></td>
            <td><?= e($row['mac_address']) ?></td>
            <td class="<?= $is_online ? 'online' : 'offline' ?>">
                <?= $is_online ? 'Online' : 'Offline' ?>
            </td>
            <td>
                <a href="edit_client.php?id=<?= $row['client_id'] ?>" class="btn edit">Update</a>
                <a href="delete_client.php?id=<?= $row['client_id'] ?>" class="btn delete deleteBtn">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- MODAL -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <p>Delete this client?</p>
        <br>
        <button id="confirmDelete" class="btn delete">Yes</button>
        <button id="cancelDelete" class="btn">Cancel</button>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
let deleteUrl = '';

document.querySelectorAll('.deleteBtn').forEach(btn=>{
    btn.onclick = e=>{
        e.preventDefault();
        deleteUrl = btn.href;
        document.getElementById('deleteModal').style.display='flex';
    };
});

document.getElementById('confirmDelete').onclick=()=>{
    window.location.href = deleteUrl;
};

document.getElementById('cancelDelete').onclick=()=>{
    document.getElementById('deleteModal').style.display='none';
};

function showToast(msg){
    const t=document.getElementById('toast');
    t.textContent=msg;
    t.classList.add('show','success');
    setTimeout(()=>t.classList.remove('show'),3000);
}

document.getElementById('searchInput').addEventListener('keyup',function(){
    let val=this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(r=>{
        r.style.display=r.innerText.toLowerCase().includes(val)?'':'none';
    });
});

<?php if ($toast === 'added'): ?>
showToast("Client added successfully");
<?php elseif ($toast === 'deleted'): ?>
showToast("Client deleted successfully");
<?php elseif ($toast === 'updated'): ?>
showToast("Client updated successfully");
<?php endif; ?>
</script>

</body>
</html>