
const slider = document.querySelector('.slider');
const nav = document.querySelector('.nav');

nav.addEventListener('click', activate);

function activate(e) {
    const items = document.querySelectorAll('.item');
    if (e.target.matches('.next')) {
        slider.append(items[0]);
    } else if (e.target.matches('.prev')) {
        slider.prepend(items[items.length - 1]);
    }
}

function displayText(id) {
    var texts = document.getElementsByClassName("text-content");
    for (var i = 0; i < texts.length; i++) {
        texts[i].style.display = "none";
    }
    document.getElementById(id).style.display = "block";

    var lis = document.querySelectorAll(".navmenu ul li");
    lis.forEach(function (li) {
        li.classList.remove("active");
    });

    document.getElementById("item" + id.charAt(4)).classList.add("active");
}


function openCity(evt, cityName, videoSrc) {
    var i, tabcontent, tablinks;
  
    // Hide all tab content
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
  
    // Remove "active" class from all tablinks
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
  
    // Show the current tab content
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
  
    // Update the video source
    var videoPlayer = document.getElementById("videoPlayer");
    videoPlayer.src = videoSrc;
    videoPlayer.load();  // Load the new video source
    videoPlayer.play();  // Play the new video
  }

  

  function openPage(pageName,elmnt) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent1");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablink1");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].style.backgroundColor = "";
    }
    document.getElementById(pageName).style.display = "block";
  }
  
  // Get the element with id="defaultOpen" and click on it
  document.getElementById("defaultOpen").click();

  document.addEventListener('DOMContentLoaded', function () {
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    dropdownItems.forEach(item => {
        item.addEventListener('click', function (event) {
            event.preventDefault();
            
            // Get the target section
            const target = document.querySelector(this.getAttribute('data-target'));
            
            // Hide all sections
            document.querySelectorAll('.collapse').forEach(collapse => {
                if (collapse !== target) {
                    $(collapse).collapse('hide');
                }
            });
            
            // Show the target section
            $(target).collapse('toggle');
        });
    });
});


document.addEventListener("DOMContentLoaded", function() {
  const countUpElements = document.querySelectorAll('.count-up');

  countUpElements.forEach(element => {
    const target = parseInt(element.getAttribute('data-target'), 10);
    let count = 0;
    const increment = Math.ceil(target / 100); // Adjust increment for smooth counting

    const interval = setInterval(() => {
      count += increment;
      if (count >= target) {
        count = target;
        clearInterval(interval);
      }
      element.textContent = count.toLocaleString(); // Format number with commas
    }, 10); // Adjust speed by changing the interval
  });
});