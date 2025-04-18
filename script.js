function theodoi() {
    window.location.href = "theodoi.php";
}
function toggleThongBao() {
    const container = document.getElementById("thongbao-container");
    container.style.display = (container.style.display === "none" || container.style.display === "") ? "block" : "none";
    
}
