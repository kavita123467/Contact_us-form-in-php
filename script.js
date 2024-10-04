document.addEventListener('DOMContentLoaded', function() {
  // Get the form element
  const form = document.querySelector('form');  
  
  // Check if the form exists before adding an event listener
  if (form) {
     // Add submit event listener to the form
     form.addEventListener('submit', function(event) {  
        event.preventDefault(); // Prevent the default form submission
        
        const formData = new FormData(form); // Gather form data
        
        // Send the form data to 'check_availability.php' using Fetch API
        fetch('check_availability.php', {  
           method: 'POST',  
           body: formData  
        })
        .then(response => {
           // Handle network errors
           if (!response.ok) {
              throw new Error('Network response was not ok');
           }
           return response.json(); // Expecting JSON response for validation errors or success
        })
        .then(data => {
           if (data.success) {
              console.log("Appointment booked successfully!");
              // You can redirect to a success page or show a success message here
              window.location.href = 'success_page.php';  // Optional: redirect to a success page
           } else if (data.errors) {
              // Display validation errors on the page
              const errorContainer = document.getElementById('error-messages');
              errorContainer.innerHTML = ''; // Clear previous errors
              data.errors.forEach(error => {
                 const errorElement = document.createElement('p');
                 errorElement.textContent = error;
                 errorElement.style.color = 'red';
                 errorContainer.appendChild(errorElement);
              });
           }
        })
        .catch(error => {
           console.error('There was a problem with the fetch operation:', error);
        });
     });
  } else {
     console.error('Form not found'); // Log error if form is not found
  }

  // Get the refresh captcha button
  const refreshButton = document.getElementById('refresh-captcha');  
  if (refreshButton) {  
     // Add click event listener to refresh the captcha image
     refreshButton.addEventListener('click', function() {  
        // Refresh the captcha image by appending a random number to the URL
        const captchaImage = document.getElementById('captcha-image');
        if (captchaImage) {
           captchaImage.src = 'captcha.php?' + Math.random();  
        } else {
           console.error('Captcha image not found'); // Log error if captcha image is not found
        }
     });  
  } else {  
     console.error('Refresh button not found'); // Log error if refresh button is not found
  }  

  // Check if the page was reloaded
  const navigationEntries = performance.getEntriesByType('navigation');  
  if (navigationEntries.length > 0 && navigationEntries[0].type === 'reload') {  
     console.log("Page reloaded");  
  } else {  
     console.info("This page is not reloaded");  
  }  
});
$.ajax({  
   type: 'POST',  
   url: 'check_availability.php',  
   data: 'contact',  
   dataType: 'json',  
   success: function(response) {  
      if (response.status === 'success') {  
        alert(response.message);  
      } else {  
        alert(response.message);  
      }  
   }  
});