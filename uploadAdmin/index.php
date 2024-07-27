<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
    integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG"
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY+/qpuqC4CAa9rOGUstpomtqpuNWT9wdPEn2fk"
    crossorigin="anonymous"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="css\admin.css"/>

    <title>school management system</title>
</head>
<body>
    <div class="admin-top">
        <div class="row">
            <div class="admin-left" id='slideNav'>
                <div class="admin">
                    <!-- Admin content here -->
                    <img src="D:\MY DOWNLOAD/IMG_20230512_215810_710.png" alt="profile picture"/>
                    <h1 style='text-align:center'>zelalem tadese admas</h1>
                </div>
                <div class="tab">
                    <!-- Tab content here -->
                    <div class="tablinks" id="defaultOpen">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="tooltip">Dashboard</span>
                        <h4>Dashboard</h4>
                    </div>
                    <div class="tablinks">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="tooltip">teacher</span>
                        <h4>Teacher</h4>
                    </div>
                </div>
            </div>
            <!--right side of dashboard start here-->
            <div class="admin-right">
                <div id="dashboard-top" class='tabcontent'>
                    <h2>dashboard content</h2>
                </div>
                <div id="teacher-top" class='tabcontent'>
                    <h2>teacher content</h2>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
