function showScreenshot(event) {
  event.preventDefault(); // Prevent form submission

  var url = document.getElementById("urlInput").value;
  var screenshotContainer = document.getElementById("screenshotContainer");
  var webCapture = document.getElementById("webCapture");
  var laptopView = document.querySelector(".laptop-view");

  // Add loading class to show the loader
  laptopView.classList.add("loading");

  // Remove previously appended webCapture image
  webCapture.innerHTML = "";

  // Show the screenshot container
  screenshotContainer.style.display = "block";

  // Get the dimensions of the image based on the viewport
  var imgElement = document.querySelector(".laptop-image");
  var imgRect = imgElement.getBoundingClientRect();
  var imgHeight = imgRect.height;
  var imgWidth = imgRect.width;

  var img = document.createElement("img");
  img.src =
    "https://api.screenshotmachine.com/?key=f7ee5e&url=" +
    encodeURIComponent(url) +
    `&dimension=${imgWidth * 2}x${imgHeight * 2 - 5}`;

  img.classList.add("screenCapture");
  webCapture.appendChild(img);

  // Hide the loader after the image is loaded
  img.onload = function () {
    laptopView.classList.remove("loading");

    // Perform SEO analysis
    performSEOAnalysis(url);
  };
}

function performSEOAnalysis(url) {
  // Construct the URL with the URL parameter
  var endpoint = "seo_analysis.php"; // Update with the actual PHP script filename or endpoint
  var requestUrl = endpoint + "?url=" + encodeURIComponent(url);

  // Send a request to fetch the HTML content of the URL
  fetch(requestUrl)
    .then(function (response) {
      if (response.ok) {
        return response.json();
      } else {
        throw new Error("Error: " + response.status);
      }
    })
    .then(function (analysisResult) {
      // Process the analysis result as needed
      console.log(analysisResult);
    })
    .catch(function (error) {
      // Handle errors
      console.log(error);
    });
}
