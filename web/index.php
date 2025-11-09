<?php
// Include database connection
require_once '../Admin/connection.php';

// Get user ID from URL parameter
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_data = null;
$error_message = '';

session_start();
$_SESSION['user_id']=$user_id;


if ($user_id <= 0) {
    $error_message = "Invalid user ID provided.";
} else {
    try {
        // Fetch user data by ID
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
        } else {
            $error_message = "User not found.";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $error_message = "Error fetching user data.";
    }
}

// Function to get proper logo URL
function getLogoUrl($logo_url) {
    if (empty($logo_url)) {
        return null;
    }
    
    // Convert relative path to proper URL from web folder
    // If logo_url is like "uploads/logos/filename.jpg", convert to "../Admin/uploads/logos/filename.jpg"
    if (strpos($logo_url, 'uploads/') === 0) {
        return '../Admin/' . $logo_url;
    } elseif (strpos($logo_url, 'Admin/uploads/') !== false) {
        return '../' . $logo_url;
    } else {
        return '../Admin/uploads/logos/' . basename($logo_url);
    }
}

$logo_url = getLogoUrl($user_data['logo_url']);

?>

<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="icon" href="<?php echo htmlspecialchars($logo_url); ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600&display=swap" rel="stylesheet">

    <style>

  .notice-board {
      max-width: 800px;
      margin: auto;
    }

    .notice-header {
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 20px;
      color: black;
      letter-spacing: 1px;
    }

    .notice-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
      display: flex;
      align-items: center;
      justify-content: space-between;
      width:600px !important;
    }

    .notice-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .notice-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .notice-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      background: url("https://cdn-icons-png.flaticon.com/512/1827/1827504.png") no-repeat center;
      background-size: cover;
    }

    .notice-text h4 {
      margin: 0;
      font-size: 18px;
      color: #333;
    }

    .notice-text p {
      margin: 5px 0 0;
      font-size: 14px;
      color: #666;
    }

    .notice-date {
      font-size: 13px;
      color: #999;
      margin-top: 4px;
    }

    .btn-group {
      display: flex;
      gap: 10px;
    }

    .pdf-btn {
      background: none;
      border: none;
      cursor: pointer;
    }

    .pdf-btn img {
      width: 26px;
      height: 26px;
    }

    /* Popup PDF Viewer */
    .pdf-viewer {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .pdf-content {
      width: 80%;
      height: 90%;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
    }

    .pdf-content iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 22px;
      font-weight: bold;
      color: #333;
      cursor: pointer;
    }


/* Dropdown menu styles - align with .nav-container a */
.nav-container { position: relative; }
    .nav-container .dropdown {
        position: relative;
        display: flex;
        align-items: center;
    }
    .nav-container .dropdown > a.dropbtn {
        padding: 0px; /* match .nav-container a padding */
        text-decoration: none;
        color: #1a1a1a;
        font-weight: 500;
        position: relative;
        font-family: 'Hind Siliguri', sans-serif;
        transition: all 0.4s cubic-bezier(0.23, 1, 0.320, 1);
        font-size: 15px;
        letter-spacing: 0.3px;
        border-radius: 12px;
        overflow: hidden;
        
    }
    /* Morphing background effect */
    .nav-container .dropdown > a.dropbtn::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: radial-gradient(circle, rgba(0, 0, 0, 0.05) 0%, rgba(0, 0, 0, 0.02) 100%);
        border-radius: 50%;
        transition: all 0.6s cubic-bezier(0.23, 1, 0.320, 1);
        transform: translate(-50%, -50%);
        z-index: -1;
    }
    /* Sliding underline */
    .nav-container .dropdown > a.dropbtn::before {
        content: "";
        position: absolute;
        bottom: 8px;
        left: 50%;
        width: 0;
        height: 2px;
        background: #000000;
        transition: all 0.5s cubic-bezier(0.23, 1, 0.320, 1);
        transform: translateX(-50%);
    }
    .nav-container .dropdown > a.dropbtn:hover {
        color: #000000;
        transform: translateY(-2px) scale(1.02);
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    }
    .nav-container .dropdown > a.dropbtn:hover::after {
        width: 120%;
        height: 120%;
        opacity: 0.8;
    }
    .nav-container .dropdown > a.dropbtn:hover::before {
        width: 70%;
    }
    .dropdown .caret { margin-left: -3px; font-size: 22px; }
    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: #ffffff;
        min-width: 240px;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 12px;
        box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        padding: 6px 4px;
        z-index: 100;
    }
    .dropdown-content a {
        display: block;
        margin: 4px 6px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 16px;
    }
    .dropdown:hover > .dropdown-content { display: block; }

    /* Keep dropdown usable on small screens */
    @media (max-width: 768px) {
        .dropdown-content { position: static; box-shadow: none; border: none; padding: 0; }
        .dropdown-content a { margin: 0 8px 8px 8px; }
    }

    </style>

    <link rel="stylesheet" href="css/mobile-responsive.css">
    

</head>

<body>
    
    <input type="hidden" id="userId" value="<?php echo $_SESSION['user_id']; ?>">

    
    <header>
        <div class="bg-animation">
            <div class="floating-orb"></div>
            <div class="floating-orb"></div>
            <div class="floating-orb"></div>
            <div class="floating-orb"></div>
            <div class="geometric-shape"></div>
            <div class="geometric-shape"></div>
            <div class="wave-pattern"></div>
        </div>
        <div class="logo-area">
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="School Logo">
            <div class="school-name">
                <h1><?php echo $user_data["name_bn"]; ?></h3>
                <h1><?php echo $user_data["name"]; ?></h2>
            </div>
        </div>
    </header>
    <button class="mobile-toggle" onclick="toggleMenu()">☰</button>
        <div class="nav-container" id="navMenu">
            <a href="index.php?id=<?php echo $user_data['id']; ?>">হোম পেজ</a>
            
            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">আমাদের সম্পর্কে <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('InstituteInfo', this)">প্রতিষ্ঠান সম্পর্কে</a>
                    <a href="javascript:void(0)" onclick="GetWebsiteInfoList('Authority',this)">পরিচালনা কমিটি</a>
                    <a href="javascript:void(0)" onclick="GetWebsiteInfoList('Teacher',this)">শিক্ষক তালিকা</a>
                    <a href="javascript:void(0)" onclick="GetWebsiteInfoList('Staff',this)">স্টাফ তালিকা</a>
                    <a href="javascript:void(0)" onclick="GetFileList('MissionVision', this)">প্রতিষ্ঠানের লক্ষ্য ও উদ্দেশ্য</a>
                    <a href="javascript:void(0)" onclick="GetFileList('WhyStudyHere', this)">কেন এই প্রতিষ্ঠানে পড়বেন</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">একাডেমিক তথ্য <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('BookList', this)">বইয়ের তালিকা</a>
                    <a href="javascript:void(0)" onclick="GetFileList('AcademicCalendar', this)">একাডেমিক ক্যালেন্ডার</a>
                    <a href="javascript:void(0)" onclick="GetFileList('RoomCount', this)">কক্ষ সংখ্যা</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">শিক্ষার্থীর তথ্য <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('StudentCount', this)">শিক্ষার্থীর সংখ্যা</a>
                    <a href="javascript:void(0)" onclick="GetFileList('DressInfo', this)">পোষাক তথ্য</a>
                    <a href="javascript:void(0)" onclick="GetFileList('ClassRoutine', this)">ক্লাস রুটিন</a>
                    <a href="javascript:void(0)" onclick="GetFileList('ExamRoutine', this)">পরীক্ষার রুটিন</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">সুবিধা সমূহ <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('ScienceLab', this)">বিজ্ঞান ল্যাব</a>
                    <a href="javascript:void(0)" onclick="GetFileList('ComputerLab', this)">কম্পিউটার ল্যাব</a>
                    <a href="javascript:void(0)" onclick="GetFileList('Library', this)">লাইব্রেরি</a>
                    <a href="javascript:void(0)" onclick="GetFileList('Playground', this)">খেলার মাঠ</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">রেজাল্ট <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('Result', this)">একাডেমিক রেজাল্ট</a>
                    <a href="javascript:void(0)" onclick="GetFileList('BoardResultLink', this)">বোর্ড রেজাল্ট লিংক</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">ভর্তি কার্যক্রম <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('AdmissionInfo', this)">ভর্তির তথ্য</a>
                    <a href="javascript:void(0)" onclick="GetFileList('OnlineApply', this)">অনলাইন আবেদন</a>
                    <a href="javascript:void(0)" onclick="GetFileList('AdmissionForm', this)">ভর্তি ফরম</a>
                </div>
            </div>

            <div class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">গ্যালারি <span class="caret">▾</span></a>
                <div class="dropdown-content">
                    <a href="javascript:void(0)" onclick="GetFileList('Gallery', this)">ছবি গ্যালারি</a>
                    <a href="javascript:void(0)" onclick="GetFileList('VideoGallery', this)">ভিডিও গ্যালারি</a>
                </div>
            </div>

            <a href="javascript:void(0)" onclick="GetFileList('RecentNews', this)">সাম্প্রতি খবর</a>
            <a href="javascript:void(0)" onclick="GetContact('Contact', this)">যোগাযোগ</a>
            <a href="javascript:void(0)" onclick="GetContact('EmergencyContact', this)">জরুরী প্রয়োজনে যোগাযোগ</a>
        </div>
    </nav>
    
    <!-- News Bar -->
    <div class="news-bar">
        <div class="news-title">নোটিশ বোর্ড</div>
        <div class="news-text" id="typewriter"></div>
    </div>

    <br>

    <div class="slider-wrapper">
        <div class="image-slider">
            <div class="slider-container" id="sliderContainer">
                <!-- Images will be loaded here -->
            </div>
            <div class="image-caption" id="imageCaption">Loading...</div>
            <button class="slider-nav prev" onclick="sliderController.changeSlide(-1)">❮</button>
            <button class="slider-nav next" onclick="sliderController.changeSlide(1)">❯</button>
        </div>
        
        <div class="slide-indicator">
            <div class="slide-lines" id="slideLines">
                <!-- Slide indicators will be generated here -->
            </div>
            <div class="slide-counter">
                <span id="currentSlide">01</span> / <span id="totalSlides">00</span>
            </div>
        </div>

        <div class="auto-play-control" style="display:none;">
            <button class="auto-play-btn" id="autoPlayBtn" onclick="sliderController.toggleAutoPlay()">
                ▶ Start Auto Play
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        <!-- Left Column -->
        <div class="left-col">
            <!-- Image Slider -->
            
    

            
<div style="display: flex; gap: 20px; margin: 20px 0;">
    <div style="flex: 0 0 200px;">
        <img id="HeadTeacherImage" src="" alt="প্রধান শিক্ষক" style="width: 100%; height: auto; border-radius: 5px;">
    </div>
    <div style="flex: 1;">
        <p style="font-size: 30px; margin: 0 0 10px 0;">প্রধান শিক্ষকের বাণী</p>
        <div style="height: 3px; background: #e74c3c; width: 100px; margin-bottom: 15px;"></div>
        <p style="font-size: 17px;" id="HeadTeacherWords"></p>
    </div>
</div>
            
            <br>

<div style="display: flex; gap: 20px; margin: 20px 0;">
    <div style="flex: 0 0 200px;">
        <img id="PresidentImage" src="" alt="সভাপতির" style="width: 100%; height: auto; border-radius: 5px;">
    </div>
    <div style="flex: 1;">
        <p style="font-size: 30px; margin: 0 0 10px 0;">সভাপতির বাণী</p>
        <div style="height: 3px; background: #e74c3c; width: 100px; margin-bottom: 15px;"></div>
        <p style="font-size: 17px;" id="PresidentWords"></p>
    </div>
</div>
            
            
            <br>
            
            
        </div>
        <!-- Right Column -->
        <div class="right-col">
           
            <div class="teacher-card">
                <img id="HeadTeacherPhoto" src="img/headmaster.jpg" alt="Teacher Photo" height="150px !important;">
                <div class="teacher-info">
                    <h3 id="HeadTeacherName">প্রধান শিক্ষকের নাম</h3>
                    <p id="HeadTeacherDesignation">প্রধান শিক্ষক</p>
                    <p><?php echo $user_data['name_bn']; ?></p>
                </div>
            </div>
            
            <div class="teacher-card">
                <img id="PresidentPhoto" src="img/headmaster.jpg" alt="Teacher Photo" height="150px !important;">
                <div class="teacher-info">
                    <h3 id="PresidentName">সভাপতির নাম</h3>
                    <p id="PresidentDesignation">সভাপতি</p>
                    <p><?php echo $user_data['name_bn']; ?></p>
                </div>
            </div>
            
            <div class="sidebar-container">
                <div class="info-card notice-card-side">
                    <div class="card-title">
                        <img src="img/warning.png" alt="Mood Board Icon" width="40px"
                            style="vertical-align: middle; margin-right: 10px;">
                        নোটিশ বোর্ড
                    </div>
                    <div class="card-details card-notice">
                        <!-- Notices will be dynamically inserted here -->
                    </div>
                </div>
                <div class="info-card exam-card">
                    <div class="card-title">
                        <img src="img/exam.png" alt="Mood Board Icon" width="35px" style="vertical-align: middle;">
                        পরীক্ষার রুটিন
                    </div>
                    <div class="card-details card-routine">

                    </div>
                </div>

            </div>
            <!-- Dynamic Calendar -->
            <div class="calendar">
                <div class="calendar-header">
                    <button class="calendar-nav" onclick="changeMonth(-1)">❮</button>
                    <span id="monthYear"></span>
                    <button class="calendar-nav" onclick="changeMonth(1)">❯</button>
                </div>
                <table>
                    <tr>
                        <th>রবি</th>
                        <th>সোম</th>
                        <th>মঙ্গল</th>
                        <th>বুধ</th>
                        <th>বৃহ</th>
                        <th>শুক্র</th>
                        <th>শনি</th>
                    </tr>
                    <tbody id="calendarBody">
                    </tbody>
                </table>
            </div>
            <!-- Attendance Section -->
            <!-- Location Section -->
            <div class="location-section">
                <div class="location-content">
                    <div class="map-placeholder">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2921150.088064519!2d88.08442389890225!3d23.685009763037288!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3751ab5c1bcbba3f%3A0x4a7885f704ec5f26!2sBangladesh!5e0!3m2!1sen!2sbd!4v1692101234567"
                            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <p>School Location</p>
                </div>
            </div>
            <!-- Visitors Counter -->
        </div>
    </main>
    <footer>
        <!-- Left Side -->
        <div class="footer-left">
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="School Logo"><br>
            <p>© 2025 - All Rights Reserved</p>
        </div>
        <!-- Middle Side -->
        <div class="footer-middle">
            <h3>Our Address</h3>
            <p><?php echo $user_data['name']; ?></p>
            <p><?php echo $user_data['address']; ?></p>
            <p><strong>Mobile No :</strong> <?php echo $user_data['mobile_no']; ?></p>

        </div>
        <!-- Right Side -->
        <div class="footer-right">

            <ul class="footer-menu">
                <li>Developed By</li>
                Amar Campus
            </ul>
            <ul class="footer-menu">
                <li>Designed by</li>
                Amar Campus
                </a>
            </ul>
        </div>
    </footer>
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/script2.js"></script>
<script src="js/mobile-menu.js"></script>

</html>