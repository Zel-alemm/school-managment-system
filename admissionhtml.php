
<?php

error_reporting(0);
session_start();
session_destroy();

if($_SESSION['message'])
{
    $message=$_SESSION['message'];

    echo "<script type='text/javascript'> 
    alert('$message');
    </script>";
    
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lumame Secondary School</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;500;700&family=Playfair+Display:ital,wght@1,400;1,500;1,600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/themify-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/ionicons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9ZcFXc6L8wr6I49JOW9CUYbTS0Gf2+59vLgtFtxpKh0V1ek1jsv" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-wwAdP8zOplJAQ8EKRJyElPoq7RcxVd6cIj8kTsfjTt2SRSaEEhNHczNT3LwHYqX4" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./css/style.css">
  </head>
<body class="about1">
  <header class="header">
    <nav class="navbar navbar-expand-lg fixed-top">
      <div class="container-fluid">
        <img src="./image/logo/logo1.png" alt="logo">
        <button class="navbar-toggler shadow-none border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="sidebar offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
          <div class="offcanvas-header text-white border-bottom">
            <h5 class="offcanvas-title" id="offcanvasNavbarLabel"><img src="./image/logo/logo1.png" alt="logo"></h5>
            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body flex-lg-row py-4 pe-4 p-lg-0">
            <ul class="navbar-nav justify-content-center align-items-center fs-5 flex-grow-1 pe-3">
            <li class="nav-item mx-2">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link" href="about.php">About</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link " href="admissionhtml.php">Admission</a>
          </li>
          <li class="nav-item mx-2">
              <a class="nav-link " href="academics.php">Academics</a>
            </li>
           
            <li class="nav-item mx-2">
              <a class="nav-link " href="studentlife.php">Student life</a>
            </li>
            <li class="nav-item mx-2">
              <a class="nav-link " href="newsletter.php"> Newsletter</a>
            </li>
             <li class="nav-item mx-2">
              <a class="nav-link " href="calender.php">calender</a>
            </li>
            </ul>
            <hr></hr>
            <div class="d-flex flex-sm-column flex-lg-row justify-content-center align-items-center gap-3 login">
              <a href="login.php" class="text-decoration-none text-blue rounded-4 px-3 py-1">Login</a>
              <a href="login.php#registerForm" class="text-decoration-none px-3 py-1 rounded-4">Sign Up</a>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
      <div class="container admission-body mt-5">
          <h2 class="text-center my-4">Admission</h2>
  
          <!-- Admission Overview Section -->
          <div class="admission-overview mb-4">
              <h3>Admission Overview</h3>
              <p>Lumame Secondary school admission Policy sets out our amis when recruiting and admitting students, who is responsiple for admitting students, our selection and admission criteria and how we assess applications, it also details how we handle applications from students with disabilities or additional support needs including admission with advanced standing. the policy outlines how we instances of fraud or misleading information in the applications process.

                in seeking to attract applications from students with excellent academic potential, Lumame secondary school is committed to widening participations and to promoting wider access to higher education. there are no admission quotas which advantage or disadvantage any group of applicants.</p>
                </div>
  
          <!-- Application Process Section -->
          <div class="application-process mb-4">
              <h3>Application Process</h3>
              <p>The application process for [School Name] is straightforward and consists of the following steps:</p>
              <ol>
                  <li><strong>Submit an Online Application:</strong> Complete the online application form available on our website. Ensure that all required fields are filled accurately.</li>
                  <li><strong>Provide Required Documents:</strong> Upload necessary documents including transcripts, letters of recommendation, and a personal statement.</li>
                  <li><strong>Pay the Application Fee:</strong> Pay the non-refundable application fee as per the instructions provided on the application portal.</li>
                  <li><strong>Attend an Interview:</strong> Shortlisted candidates may be invited for an interview, either in person or virtually, as part of the selection process.</li>
                  <li><strong>Receive Admission Decision:</strong> Admission decisions will be communicated via email. Accepted students will receive further instructions on enrollment.</li>
              </ol>
          </div>
  
          <!-- Requirements Section -->
          <div class="requirements mb-4">
              <h3>Requirements</h3>
              <p>To be considered for admission to [School Name], applicants must meet the following requirements:</p>
              <ul>
                  <li><strong>Academic Records:</strong> Provide official transcripts from previous educational institutions, showcasing academic performance.</li>
                  <li><strong>Standardized Test Scores:</strong> Submit scores from relevant standardized tests, if applicable.</li>
                  <li><strong>Letters of Recommendation:</strong> Include letters from teachers, mentors, or employers who can speak to the applicant’s qualifications and character.</li>
                  <li><strong>Personal Statement:</strong> Write a personal statement outlining the applicant’s goals, achievements, and reasons for choosing [School Name].</li>
                  <li><strong>Proof of Identity:</strong> Provide a copy of a government-issued ID or passport for identity verification purposes.</li>
              </ul>
          </div>
  
          <div class="application">
            <div class="container">
              <div class="row  my-5">
               
                <div class="form-title text-center" data-aos="fade-right" data-aos-delay="200">
                  <h1>Please fill the form presented below for registration</h1>
                </div>
              <form action="data_check.php" method="POST" data-aos="fade-right" data-aos-delay="200">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="form-floating form-content1">
                <input type="text" id="fname" name="fname" placeholder="e.g. Zelalem" class="form-control" required/>
                <label for="fname" class="form-label">First name</label>
            </div>
            <div class="form-floating form-content1">
                <input type="text" id="mname" name="mname" placeholder="e.g. Zelalem" class="form-control"/>
                <label for="mname" class="form-label">Middle name</label>
            </div>
            <div class="form-floating form-content1">
                <input type="text" id="lname" name="lname" placeholder="e.g. Zelalem" class="form-control"/>
                <label for="lname" class="form-label">Last name</label>
            </div>
            <div class="form-floating form-content1">
                <input type="number" id="age" name="age" placeholder="e.g. 19" class="form-control" required/>
                <label for="age" class="form-label">Age</label>
            </div>
            <div class="form-floating form-content1">
                <input type="email" id="email" name="email" placeholder="e.g. zel@gmail.com" class="form-control" required/>
                <label for="email" class="form-label">Email</label>
            </div>
            <div class="form-floating form-content1">
                <input type="tel" id="phone" name="phone" placeholder="e.g. +251905487849" class="form-control" required/>
                <label for="phone" class="form-label">Phone</label>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="form-content2" data-aos="fade-right" data-aos-delay="200">
                <label for="grade" class="form-label">Grade</label>
                <select name="grade" id="grade" class="form-select" required>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
            </div>
            <div class="form-content2" data-aos="fade-right" data-aos-delay="200">
                <label for="stream" class="form-label">Stream</label>
                <select name="stream" id="stream" class="form-select" required>
                    <option value="Natural">Natura Science</option>
                    <option value="Social">Social Science</option>
                    <option value="Non-stream">Non-stream</option>
                </select>
            </div>
            <div class="form-floating write-text form-content2">
                <textarea name="message" id="message" style="height: 150px;" class="form-control" placeholder="Write a message" required></textarea>
                <label for="message">Write a message</label>
            </div>
            <div class="text-center form-content3">
                <button type="submit" name="apply" class="btn btn-success">Submit</button>
            </div>
        </div>
    </div>
</form>


             
            </div>
            </div>
          </div>
        
      </div>

  
   
  
   
  

  <footer class="footer">
    <div class="row">
      <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="footer-img">
          <img class="logo" src="./image/logo/logo1.png" alt="logo">
        </div>
      </div>
      <div class="col-lg-6 col-md-6 col-sm-12">
        <ul class="footer-navbar">
        <li class="nav-item mx-2">
            <a class="nav-link" href="index.php">Home</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link" href="about.php">About</a>
          </li>
          <li class="nav-item mx-2">
            <a class="nav-link " href="admissionhtml.php">Admission</a>
          </li>
          <li class="nav-item mx-2">
              <a class="nav-link " href="academics.php">Academics</a>
            </li>
           
            <li class="nav-item mx-2">
              <a class="nav-link " href="studentlife.php">Student life</a>
            </li>
            <li class="nav-item mx-2">
              <a class="nav-link " href="newsletter.php"> Newsletter</a>
            </li>
             <li class="nav-item mx-2">
              <a class="nav-link " href="calender.php">calender</a>
            </li>
        </ul>
      </div>
    </div>
    <div class="row contact-row">
      <div class="col-lg-10 col-md-8 col-sm-10">
        <ul class="ulleft text-center">
          <li>
            <i class="far fa-envelope"></i>
            <a href="mailto:lumamesc@gmail.com">lumamesc@gmail.com</a>
            <span>|</span>
          </li>
          <li>
            <i class="fas fa-phone-volume"></i>
            <a href="tel:+2519123456">+2519123456</a>
          </li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-4 col-sm-2 n">
        <a class="btn" href="#top">Back to top</a>
      </div>
    </div>
    <div class="copyright text-center">
      <hr class="footer-divider">
      <p>
        &copy; <script>document.write(new Date().getFullYear());</script> <a href="#" class="copyright-link">Lumame Secondary School</a> / powered by Zelalem Tadese
      </p>
    </div>
  </footer>
 <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9ZcFXc6L8wr6I49JOW9CUYbTS0Gf2+59vLgtFtxpKh0V1ek1jsv" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-ENiC1Ye+mH3Z2aAa9P6egjS92W2Uk4s5vTZ9dwyVxbEO3I5W3V2Z7BNoeUPQ6JubE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      AOS.init();
      AOS.refresh();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
  <script src="./js/script.js"></script>
</body>
</html>
