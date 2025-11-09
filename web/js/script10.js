var userId = document.getElementById("userId").value;

// Optimized Global Variables with better performance
const APP_CONFIG = {
    slideIndex: 0,
    currentMonthIndex: new Date().getMonth(),
    currentYear: new Date().getFullYear(),
    captions: [],
    months: ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"],
    bengaliDigits: ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
    newsItems: [". . . ."] // will be loaded dynamically via AJAX
};

// Cache DOM elements for better performance
const DOM_CACHE = {};

function cacheElement(id) {
    if (!DOM_CACHE[id]) {
        DOM_CACHE[id] = document.getElementById(id);
    }
    return DOM_CACHE[id];
}

// Function to load Notice items via AJAX
function loadNotices() {
    fetch("API/GetFileList.php?type=Notice&user_id="+userId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.files.length > 0) {
                // Replace newsItems with Notice descriptions
                APP_CONFIG.newsItems = data.files.map(file => file.description);
                console.log("Notices loaded:", APP_CONFIG.newsItems);
                // 👉 You can call your function here to render notices in UI
                renderNotices();
            } else {
                console.warn("No notices found.");
            }
        })
        .catch(error => console.error("Error loading notices:", error));
}

// Example function to display notices in an element with id="news-container"
function renderNotices() {
    const container = document.getElementById("news-container");
    if (!container) return;

    container.innerHTML = "";
    APP_CONFIG.newsItems.forEach((item, index) => {
        const li = document.createElement("li");
        li.textContent = item;
        container.appendChild(li);
    });
    TypewriterController.start();
}

// UNIFIED Slider Controller with Auto-Play (Fixed)
const SliderController = (() => {
    let container, caption, slideLines, currentSlideEl, totalSlidesEl, autoPlayBtn;
    let slideIndex = 0;
    let sliderImages = [];
    let sliderCaptions = [];
    let isTransitioning = false;
    let autoPlayInterval = null;
    let autoPlayDelay = 3000; // 3 seconds

    const init = () => {
        // Cache DOM elements
        container = cacheElement('sliderContainer');
        caption = cacheElement('imageCaption');
        slideLines = cacheElement('slideLines');
        currentSlideEl = cacheElement('currentSlide');
        totalSlidesEl = cacheElement('totalSlides');
        autoPlayBtn = cacheElement('autoPlayBtn');

        // Enable GPU acceleration
        if (container) {
            container.style.willChange = 'transform';
            container.style.transform = 'translate3d(0, 0, 0)';
        }

        // Load images from your API
        loadSliderImages();
    };

    // Fixed loadSliderImages function
    const loadSliderImages = () => {
        fetch("API/GetFileList.php?type=SliderImage&user_id="+userId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.files.length > 0) {
                    container.innerHTML = "";
                    sliderImages = [];
                    sliderCaptions = [];
                    
                    data.files.forEach((file, index) => {
                        const img = document.createElement("img");
                        img.src = "../Admin/" + file.file_url;
                        img.alt = file.description || "Slider Image";
                        img.style.opacity = '0';
                        
                        // Fade in when loaded
                        img.onload = () => {
                            setTimeout(() => {
                                img.style.transition = 'opacity 0.5s ease';
                                img.style.opacity = '1';
                            }, index * 100);
                        };

                        container.appendChild(img);
                        sliderImages.push(img);
                        sliderCaptions.push(img.alt);
                    });

                    setupSlideIndicators();
                    if (totalSlidesEl) {
                        totalSlidesEl.textContent = String(sliderImages.length).padStart(2, "0");
                    }
                    slideIndex = 0;
                    showSlide(slideIndex);
                    
                    if (caption) {
                        caption.textContent = "Ready to slide!";
                    }

                    // Auto-start after loading
                    setTimeout(() => {
                        startAutoPlay();
                    }, 2000);
                } else {
                    console.warn("No slider images found.");
                    if (caption) caption.textContent = "No images available";
                }
            })
            .catch(error => {
                console.error("Error loading slider images:", error);
                if (caption) caption.textContent = "Error loading images";
            });
    };

    const setupSlideIndicators = () => {
        if (!slideLines) return;
        
        slideLines.innerHTML = '';
        sliderImages.forEach((_, index) => {
            const line = document.createElement('div');
            line.className = 'slide-line';
            line.onclick = () => goToSlide(index);
            slideLines.appendChild(line);
        });
    };

    const showSlide = (index) => {
        if (!sliderImages.length || isTransitioning || !container) return;

        isTransitioning = true;
        slideIndex = index;

        // Ensure index is within bounds
        if (slideIndex >= sliderImages.length) slideIndex = 0;
        if (slideIndex < 0) slideIndex = sliderImages.length - 1;

        const translateX = -(slideIndex * 100);

        requestAnimationFrame(() => {
            container.style.transform = `translate3d(${translateX}%, 0, 0)`;
            
            // Update caption with fade effect
            if (caption) {
                caption.style.opacity = '0';
                setTimeout(() => {
                    caption.textContent = sliderCaptions[slideIndex];
                    caption.style.transition = 'opacity 0.3s ease';
                    caption.style.opacity = '1';
                }, 150);
            }

            // Update indicators
            document.querySelectorAll('.slide-line').forEach((dot, i) => {
                dot.classList.toggle('active', i === slideIndex);
            });

            // Update counter
            if (currentSlideEl) {
                currentSlideEl.textContent = String(slideIndex + 1).padStart(2, '0');
            }

            setTimeout(() => { isTransitioning = false; }, 600);
        });
    };

    const changeSlide = (direction) => {
        if (isTransitioning) return;
        showSlide(slideIndex + direction);
    };

    const goToSlide = (index) => {
        if (isTransitioning) return;
        showSlide(index);
    };

    const startAutoPlay = () => {
        if (autoPlayInterval) return;
        
        autoPlayInterval = setInterval(() => {
            changeSlide(1);
        }, autoPlayDelay);
        
        if (autoPlayBtn) {
            autoPlayBtn.textContent = '⏸ Pause Auto Play';
            autoPlayBtn.classList.add('playing');
        }
    };

    const stopAutoPlay = () => {
        if (autoPlayInterval) {
            clearInterval(autoPlayInterval);
            autoPlayInterval = null;
        }
        
        if (autoPlayBtn) {
            autoPlayBtn.textContent = '▶ Start Auto Play';
            autoPlayBtn.classList.remove('playing');
        }
    };

    const toggleAutoPlay = () => {
        if (autoPlayInterval) {
            stopAutoPlay();
        } else {
            startAutoPlay();
        }
    };

    const updateSlider = () => {
        showSlide(slideIndex);
    };

    const currentSlide = (index) => {
        goToSlide(index - 1);
    };

    // Public API
    return {
        init,
        changeSlide,
        goToSlide,
        currentSlide,
        updateSlider,
        startAutoPlay,
        stopAutoPlay,
        toggleAutoPlay
    };
})();

// Global loadSliderImages function (for compatibility)
function loadSliderImages() {
    SliderController.init();
}

// Head Teacher Data Load
$(document).ready(function() {
    // Make AJAX request to the API
    $.ajax({
        url: 'API/GetWebsiteInfoList.php',
        type: 'GET',
        data: { user_id: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                // Find the head teacher (is_head_teacher = 1)
                var headTeacher = response.data.find(function(teacher) {
                    return teacher.is_head_teacher == 1;
                });
                
                if (headTeacher) {
                    // Update the HTML elements with head teacher data
                    $('#HeadTeacherName').text(headTeacher.name);
                    $('#HeadTeacherDesignation').text(headTeacher.designation);
                    
                    // Update the photo src - add 'Admin/' prefix if needed
                    var photoSrc = headTeacher.image_url;
                    $('#HeadTeacherPhoto').attr('src', photoSrc);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching teacher data:', error);
        }
    });
});


// President Data Load 
$(document).ready(function() {
    // Make AJAX request to the API
    $.ajax({
        url: 'API/GetWebsiteInfoList.php',
        type: 'GET',
        data: { user_id: userId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                
                var president = response.data.find(function(authority) {
                    return authority.is_president == 1;
                });
                
                if (president) {
                    // Update the HTML elements with president data
                    $('#PresidentName').text(president.name);
                    $('#PresidentDesignation').text(president.designation);
                    
                    // Update the photo src - add 'Admin/' prefix if needed
                    var photoSrc = president.image_url;
                    $('#PresidentPhoto').attr('src', photoSrc);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching president data:', error);
        }
    });
});

// Load Notice
$(document).ready(function() {
    // Make AJAX request to get notice files
    $.ajax({
        url: 'API/GetFileList.php',
        type: 'GET',
        data: {
            type: 'Notice',
            user_id:userId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.files.length > 0) {
                // Clear the static content
                $('.card-notice').empty();
                
                // Add each notice dynamically
                $.each(response.files, function(index, notice) {
                    // Format the date
                    var noticeDate = new Date(notice.create_date);
                    var formattedDate = noticeDate.toLocaleDateString('bn-BD', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    // Create notice item
                    var noticeItem = $('<div>').addClass('notice-item');
                    
                    // Create date element
                    var dateElement = $('<div>').addClass('notice-date')
                        .append(
                            $('<img>').attr({
                                'src': 'img/schedule.png',
                                'alt': 'Schedule Icon',
                                'width': '20px'
                            }).css({
                                'vertical-align': 'middle',
                                'margin-right': '5px'
                            })
                        )
                        .append(formattedDate);
                    
                    // Create text element with download link
                    var textElement = $('<div>').addClass('notice-text');
                    
                    if (notice.file_url) {
                        // If file exists, make it a download link
                        var downloadLink = $('<a>').attr({
                            'href': '../Admin/' + notice.file_url,
                            'target': '_blank',
                            'download': ''
                        }).text(notice.description);
                        
                        textElement.append(downloadLink);
                    } else {
                        // If no file, just show description
                        textElement.text(notice.description);
                    }
                    
                    // Append elements to notice item
                    noticeItem.append(dateElement).append(textElement);
                    
                    // Add to notice board
                    $('.card-notice').append(noticeItem);
                });
            } else {
                // Show message if no notices found
                $('.card-notice').html('<p>No notices available</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching notices:', error);
            $('.card-notice').html('<p>Error loading notices</p>');
        }
    });
});

// Load Routine
$(document).ready(function() {
    // Make AJAX request to get notice files
    $.ajax({
        url: 'API/GetFileList.php',
        type: 'GET',
        data: {
            type: 'ExamRoutine',
            user_id: userId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.files.length > 0) {
                // Clear the static content
                $('.card-routine').empty();
                
                // Add each notice dynamically
                $.each(response.files, function(index, notice) {
                    // Format the date
                    var noticeDate = new Date(notice.create_date);
                    var formattedDate = noticeDate.toLocaleDateString('bn-BD', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    // Create notice item
                    var noticeItem = $('<div>').addClass('notice-item');
                    
                    // Create date element
                    var dateElement = $('<div>').addClass('notice-date')
                        .append(
                            $('<img>').attr({
                                'src': 'img/schedule.png',
                                'alt': 'Schedule Icon',
                                'width': '20px'
                            }).css({
                                'vertical-align': 'middle',
                                'margin-right': '5px'
                            })
                        )
                        .append(formattedDate);
                    
                    // Create text element with download link
                    var textElement = $('<div>').addClass('notice-text');
                    
                    if (notice.file_url) {
                        // If file exists, make it a download link
                        var downloadLink = $('<a>').attr({
                            'href': '../Admin/' + notice.file_url,
                            'target': '_blank',
                            'download': ''
                        }).text(notice.description);
                        
                        textElement.append(downloadLink);
                    } else {
                        // If no file, just show description
                        textElement.text(notice.description);
                    }
                    
                    // Append elements to notice item
                    noticeItem.append(dateElement).append(textElement);
                    
                    // Add to notice board
                    $('.card-routine').append(noticeItem);
                });
            } else {
                // Show message if no notices found
                $('.card-routine').html('<p>No routine available</p>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching routine:', error);
            $('.card-routine').html('<p>Error loading routine</p>');
        }
    });
});

// Load Files
function GetFileList(type, element) {
    fetch(`API/GetFileList.php?type=${encodeURIComponent(type)}&user_id=${encodeURIComponent(userId)}`)
        .then(response => response.json())
        .then(data => {
            const leftCol = document.querySelector(".left-col");
            leftCol.innerHTML = ""; // clear old content

            // Main container
            const board = document.createElement("div");
            board.classList.add("notice-board");

            // Header
            const header = document.createElement("div");
            header.classList.add("notice-header");
            header.innerHTML = `<img src="img/mood-board.png" width="50px" style="vertical-align: middle; margin-right: 10px; margin-bottom: 18px;">${element.textContent}`;
            board.appendChild(header);

            if (!data.success || data.count === 0) {
                const msg = document.createElement("p");
                msg.textContent = "কোনো তথ্য পাওয়া যায়নি।";
                board.appendChild(msg);
                leftCol.appendChild(board);
                return;
            }

            // Cards
            data.files.forEach(file => {
                const card = document.createElement("div");
                card.classList.add("notice-card");

                const info = document.createElement("div");
                info.classList.add("notice-info");
                info.innerHTML = `
                    <img src="img/pdf.png" width="50px">
                    <div class="notice-text">
                        <h4>${file.type}</h4>
                        <p>${file.description}</p>
                        <div class="notice-date">${file.create_date}</div>
                    </div>
                `;

                const btnGroup = document.createElement("div");
                btnGroup.classList.add("btn-group");
                if (file.file_url && file.file_url.trim() !== "") {
                    btnGroup.innerHTML = `
                        <button class="pdf-btn" onclick="openPDF('../Admin/${file.file_url}')">
                            <img src="img/view.png" alt="View PDF" width="30px">
                        </button>
                        <a href="../Admin/${file.file_url}" download class="pdf-btn">
                            <img src="img/download.png" alt="Download PDF" width="30px">
                        </a>
                    `;
                } else {
                    btnGroup.innerHTML = ""; // No buttons if file_url is null/empty
                }
                card.appendChild(info);
                card.appendChild(btnGroup);
                board.appendChild(card);
            });

            leftCol.appendChild(board);

            // PDF Viewer Modal (append once if not exists)
            if (!document.getElementById("pdfViewer")) {
                const modal = document.createElement("div");
                modal.classList.add("pdf-viewer");
                modal.id = "pdfViewer";
                modal.innerHTML = `
                    <div class="pdf-content">
                        <span class="close-btn" onclick="closePDF()">×</span>
                        <iframe id="pdfFrame"></iframe>
                    </div>
                `;
                document.body.appendChild(modal);
            }
        })
        .catch(error => {
            console.error("Error loading file list:", error);
            const leftCol = document.querySelector(".left-col");
            leftCol.innerHTML = "<p>লোড করতে সমস্যা হয়েছে।</p>";
        });
}

// PDF Modal Functions
function openPDF(url) {
    document.getElementById("pdfFrame").src = url;
    document.getElementById("pdfViewer").style.display = "flex";
}
function closePDF() {
    document.getElementById("pdfFrame").src = "";
    document.getElementById("pdfViewer").style.display = "none";
}

// Get Contact
function GetContact(type, element) {
    fetch(`API/GetContact.php?type=${encodeURIComponent(type)}&user_id=${encodeURIComponent(userId)}`)
        .then(res => res.json())
        .then(data => {
            const leftCol = document.querySelector(".left-col");
            leftCol.innerHTML = ""; // clear old content

            const container = document.createElement("div");
            container.classList.add("contact-card");

            // Header
            const header = document.createElement("div");
            header.classList.add("notice-header");
            header.innerHTML = `<img src="img/mood-board.png" width="50px" style="vertical-align: middle; margin-right: 10px; margin-bottom: 18px;">${element.textContent}`;
            container.appendChild(header);

            if (!data.success) {
                const msg = document.createElement("p");
                msg.textContent = data.message || "No user info available.";
                container.appendChild(msg);
                leftCol.appendChild(container);
                return;
            }

            const user = data.user;

            // User Info
            const card = document.createElement("div");
            card.classList.add("notice-card");

            card.innerHTML = `
                <div class="notice-info">
                    <img src="../Admin/${user.logo_url || 'default.png'}" width="80px" style="border-radius: 8px;">
                    <div class="notice-text">
                        <h4>${user.name_bn || user.name}</h4>
                        <p>EIIN: ${user.eiin_no || "N/A"}</p>
                        <p>Mobile: ${user.mobile_no}</p>
                        <p>Address: ${user.address || "N/A"}</p>
                        <div class="notice-date">তৈরী: ${user.create_date}</div>
                    </div>
                </div>
            `;

            container.appendChild(card);
            leftCol.appendChild(container);
        })
        .catch(err => {
            console.error("Error fetching contact info:", err);
            const leftCol = document.querySelector(".left-col");
            leftCol.innerHTML = "<p>কনট্যাক্ট লোড করতে সমস্যা হয়েছে।</p>";
        });
}

// Get WebsiteInfoList
function GetWebsiteInfoList(type, el) {
    const leftCol = document.getElementsByClassName("left-col")[0]; // ✅ pick first
    leftCol.innerHTML = "<p>লোড হচ্ছে...</p>";

    fetch(`API/GetWebsiteInfoList.php?type=${encodeURIComponent(type)}&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            leftCol.innerHTML = "";

            if (!data.success || data.count === 0) {
                leftCol.innerHTML = "<p>কোন তথ্য পাওয়া যায়নি।</p>";
                return;
            }

            data.data.forEach(item => {
                const imgUrl = item.image_url && item.image_url.trim() !== "" 
                               ? item.image_url 
                               : "img/no-profile.png";

                let badge = "";
                if (item.is_head_teacher == 1) {
                    badge = `<span class="badge head-teacher">প্রধান শিক্ষক</span>`;
                } else if (item.is_president == 1) {
                    badge = `<span class="badge president">সভাপতি</span>`;
                }

                const card = document.createElement("div");
                card.className = "teacher-card";
                card.innerHTML = `
                    <img src="${imgUrl}" alt="${item.name}" class="teacher-img">
                    <div class="teacher-info">
                        <h3>${item.name}</h3>
                        <p>${item.designation || ""}</p>
                        <p>📞 ${item.mobile_no || "N/A"}</p>
                        ${badge}
                    </div>
                `;

                leftCol.appendChild(card); // ✅ works now
            });
        })
        .catch(error => {
            console.error("Error loading website info:", error);
            leftCol.innerHTML = "<p>ডাটা লোড করতে সমস্যা হয়েছে।</p>";
        });
}

// Optimized Mobile Menu Toggle with GPU acceleration
const toggleMenu = (() => {
    let isOpen = false;
    return () => {
        const menu = cacheElement('navMenu');
        if (!menu) return;
        
        isOpen = !isOpen;
        menu.style.transform = isOpen ? 'translateX(0)' : 'translateX(-100%)';
        menu.classList.toggle('show', isOpen);
    };
})();

// Fast Accordion Toggle with single reflow
function toggleBox(header) {
    if (!header?.parentElement) return;
    
    const box = header.parentElement;
    const content = box.querySelector('.box-content');
    const isOpen = box.classList.contains('open');
    
    // Use transform instead of display for better performance
    requestAnimationFrame(() => {
        box.classList.toggle('open', !isOpen);
        if (content) {
            content.style.maxHeight = !isOpen ? content.scrollHeight + 'px' : '0';
            content.style.opacity = !isOpen ? '1' : '0';
        }
    });
}

// Optimized Calendar with minimal DOM manipulation
const CalendarController = (() => {
    let calendarBody, monthYearEl;
    let calendarHTML = '';
    
    const init = () => {
        calendarBody = cacheElement('calendarBody');
        monthYearEl = cacheElement('monthYear');
    };
    
    const generateCalendar = (month, year) => {
        if (!calendarBody) return;
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();
        
        let html = '';
        let date = 1;
        
        // Build HTML string in memory (faster than DOM manipulation)
        for (let i = 0; i < 6; i++) {
            html += '<tr>';
            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < firstDay) {
                    html += '<td></td>';
                } else if (date > daysInMonth) {
                    break;
                } else {
                    const isToday = (date === today.getDate() && 
                                   month === today.getMonth() && 
                                   year === today.getFullYear());
                    const classes = isToday ? 'today' : (date === 15 ? 'highlight' : '');
                    html += `<td class="${classes}">${date}</td>`;
                    date++;
                }
            }
            html += '</tr>';
            if (date > daysInMonth) break;
        }
        
        // Single DOM update
        requestAnimationFrame(() => {
            calendarBody.innerHTML = html;
            if (monthYearEl) {
                monthYearEl.textContent = `${APP_CONFIG.months[month]} ${year}`;
            }
        });
    };
    
    const changeMonth = (direction) => {
        APP_CONFIG.currentMonthIndex += direction;
        
        if (APP_CONFIG.currentMonthIndex > 11) {
            APP_CONFIG.currentMonthIndex = 0;
            APP_CONFIG.currentYear++;
        } else if (APP_CONFIG.currentMonthIndex < 0) {
            APP_CONFIG.currentMonthIndex = 11;
            APP_CONFIG.currentYear--;
        }
        
        generateCalendar(APP_CONFIG.currentMonthIndex, APP_CONFIG.currentYear);
    };
    
    return { init, generateCalendar, changeMonth };
})();

// Optimized Attendance with batch updates
const AttendanceController = (() => {
    const updateAttendance = () => {
        const total = 537;
        const present = Math.floor(Math.random() * 50) + 480;
        const absent = total - present;
        
        requestAnimationFrame(() => {
            const presentEl = cacheElement('todayPresent');
            const absentEl = cacheElement('todayAbsent');
            
            if (presentEl) presentEl.textContent = present;
            if (absentEl) absentEl.textContent = absent;
        });
    };
    
    return { updateAttendance };
})();

// Fast Visitor Counter with optimized updates
const VisitorController = (() => {
    const updateVisitorCount = () => {
        const currentElement = cacheElement('visitorCount');
        if (!currentElement) return;
        
        const current = parseInt(currentElement.textContent) || 0;
        requestAnimationFrame(() => {
            currentElement.textContent = (current + 1).toString().padStart(7, '0');
        });
    };
    
    return { updateVisitorCount };
})();

// Ultra-smooth Typewriter Effect with RAF
const TypewriterController = (() => {
    let itemIndex = 0;
    let charIndex = 0;
    let typewriter;
    let animationId;
    
    const config = {
        typingSpeed: 60,
        deletingSpeed: 30,
        pauseBetween: 1500
    };
    
    const init = () => {
        typewriter = cacheElement("typewriter");
    };
    
    const typeEffect = () => {
        if (!typewriter) return;
        
        const currentText = APP_CONFIG.newsItems[itemIndex];
        if (charIndex < currentText.length) {
            typewriter.textContent = currentText.substring(0, charIndex + 1);
            charIndex++;
            animationId = setTimeout(typeEffect, config.typingSpeed);
        } else {
            animationId = setTimeout(deleteEffect, config.pauseBetween);
        }
    };
    
    const deleteEffect = () => {
        if (!typewriter) return;
        
        if (charIndex > 0) {
            typewriter.textContent = APP_CONFIG.newsItems[itemIndex].substring(0, charIndex - 1);
            charIndex--;
            animationId = setTimeout(deleteEffect, config.deletingSpeed);
        } else {
            itemIndex = (itemIndex + 1) % APP_CONFIG.newsItems.length;
            animationId = setTimeout(typeEffect, 200);
        }
    };
    
    const start = () => {
        if (typewriter) typeEffect();
    };
    
    const stop = () => {
        if (animationId) clearTimeout(animationId);
    };
    
    return { init, start, stop };
})();

// Utility Functions with better performance
const Utils = {
    toBengaliNumber: (number) => {
        return number.toString().replace(/\d/g, digit => APP_CONFIG.bengaliDigits[digit]);
    },
    
    fadeIn: (element, duration = 300) => {
        if (!element) return;
        
        element.style.opacity = '0';
        element.style.display = 'block';
        
        let start;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            
            element.style.opacity = progress;
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    throttle: (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// Enhanced Touch and Keyboard Support
const InputController = (() => {
    let startX = 0;
    let startY = 0;
    
    const initTouchSupport = () => {
        const container = cacheElement('sliderContainer');
        if (!container) return;
        
        const handleTouchStart = Utils.throttle((e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, 16);
        
        const handleTouchEnd = Utils.throttle((e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            // Only handle horizontal swipes
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 30) {
                SliderController.changeSlide(diffX > 0 ? 1 : -1);
            }
        }, 16);
        
        container.addEventListener('touchstart', handleTouchStart, { passive: true });
        container.addEventListener('touchend', handleTouchEnd, { passive: true });
    };
    
    const initKeyboardSupport = () => {
        const handleKeydown = Utils.throttle((e) => {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    SliderController.changeSlide(-1);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    SliderController.changeSlide(1);
                    break;
                case 'Escape':
                    toggleMenu();
                    break;
                case ' ': // Spacebar
                    e.preventDefault();
                    SliderController.toggleAutoPlay();
                    break;
            }
        }, 100);
        
        document.addEventListener('keydown', handleKeydown);
    };
    
    return { initTouchSupport, initKeyboardSupport };
})();

// Optimized Event Handlers
const EventHandlers = (() => {
    const initClickOutside = () => {
        const handleClickOutside = Utils.debounce((e) => {
            const nav = cacheElement('navMenu');
            const toggle = document.querySelector('.mobile-toggle');
            
            if (nav && toggle && 
                !nav.contains(e.target) && 
                !toggle.contains(e.target)) {
                nav.classList.remove('show');
            }
        }, 50);
        
        document.addEventListener('click', handleClickOutside, { passive: true });
    };
    
    const initImageLoading = () => {
        const images = document.querySelectorAll('img');
        images.forEach(img => {
            if (!img.complete) {
                img.style.opacity = '0.7';
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                    this.style.transition = 'opacity 0.3s ease';
                }, { once: true });
            }
        });
    };
    
    const initHoverControls = () => {
        const slider = document.querySelector('.image-slider');
        if (!slider) return;
        
        slider.addEventListener('mouseenter', () => {
            SliderController.stopAutoPlay();
        });
        
        slider.addEventListener('mouseleave', () => {
            SliderController.startAutoPlay();
        });
    };
    
    return { initClickOutside, initImageLoading, initHoverControls };
})();

// Global functions for backward compatibility
function changeSlide(direction) {
    SliderController.changeSlide(direction);
}

function currentSlide(index) {
    SliderController.currentSlide(index);
}

function changeMonth(direction) {
    CalendarController.changeMonth(direction);
}

// Main Initialization with performance optimization
const initializeApp = () => {
    // Use RAF for smooth initialization
    requestAnimationFrame(() => {
        // Initialize controllers
        SliderController.init();
        CalendarController.init();
        TypewriterController.init();
        
        // Generate initial calendar
        CalendarController.generateCalendar(APP_CONFIG.currentMonthIndex, APP_CONFIG.currentYear);
        
        // Start typewriter
        TypewriterController.start();
        
        // Update initial attendance
        AttendanceController.updateAttendance();
        
        // Initialize input handlers
        InputController.initTouchSupport();
        InputController.initKeyboardSupport();
        
        // Initialize event handlers
        EventHandlers.initClickOutside();
        EventHandlers.initImageLoading();
        EventHandlers.initHoverControls();
        
        // Load notices
        loadNotices();
        
        console.log('App initialized successfully');
    });
};

// Enhanced DOMContentLoaded with faster loading
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}

// Export functions for global access
window.AppFunctions = {
    toggleMenu,
    toggleBox,
    changeSlide: SliderController.changeSlide,
    currentSlide: SliderController.currentSlide,
    changeMonth: CalendarController.changeMonth,
    updateAttendance: AttendanceController.updateAttendance,
    updateVisitorCount: VisitorController.updateVisitorCount,
    ...Utils
};

// Performance monitoring
if (typeof performance !== 'undefined') {
    window.addEventListener('load', () => {
        console.log(`Page loaded in ${Math.round(performance.now())}ms`);
    });
}



// Version with jQuery (if you prefer to use jQuery like your other functions)
function loadWordsJQuery() {
    // Load Head Teacher Words
    $.ajax({
        url: 'API/GetFileList.php',
        type: 'GET',
        data: { type: 'HeadTeacherWords',user_id:userId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.files.length > 0) {
                $('#HeadTeacherWords').text(response.files[0].description || 'No message available');
            } else {
                $('#HeadTeacherWords').text('প্রধান শিক্ষকের বাণী লোড করা যায়নি।');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading Head Teacher words:', error);
            $('#HeadTeacherWords').text('বাণী লোড করতে সমস্যা হয়েছে।');
        }
    });

    // Load President Words
    $.ajax({
        url: 'API/GetFileList.php',
        type: 'GET',
        data: { type: 'PresidentWords',user_id:userId },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.files.length > 0) {
                $('#PresidentWords').text(response.files[0].description || 'No message available');
            } else {
                $('#PresidentWords').text('সভাপতির বাণী লোড করা যায়নি।');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading President words:', error);
            $('#PresidentWords').text('বাণী লোড করতে সমস্যা হয়েছে।');
        }
    });
}

// Call the function when the DOM is ready
$(document).ready(function() {
    loadWordsJQuery(); // or loadWordsAsync() or loadWordsJQuery()
});