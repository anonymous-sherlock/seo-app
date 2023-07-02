function showScreenshot(event) {
  event.preventDefault(); // Prevent form submission
  var url = document.getElementById("urlInput").value;
  var webCapture = document.getElementById("screen-shot");
  var reportDate = document.getElementById("generated-report-date");
  reportDate.innerHTML = generateReportDate();

  // Remove previously appended webCapture image
  webCapture.innerHTML = "";

  // Get the dimensions of the image based on the viewport
  var imgElement = document.querySelector(".mac-view");
  var imgRect = imgElement.getBoundingClientRect();
  var imgHeight = imgRect.height;
  var imgWidth = imgRect.width;

  var img = document.createElement("img");
  img.src =
    "https://api.screenshotmachine.com/?key=f7ee5e&url=" +
    encodeURIComponent(url) +
    `&dimension=${imgWidth * 3}x${imgHeight * 3}`;

  img.classList.add("w-100");
  webCapture.appendChild(img);

  // Hide the loader after the image is loaded
  img.onload = function () {
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
      alert("An error occurred: " + error.message);
    });
}

function generateReportDate() {
  const currentDate = new Date();
  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  const month = monthNames[currentDate.getMonth()];
  const day = currentDate.getDate();
  const year = currentDate.getFullYear();
  let hour = currentDate.getHours();
  const minute = currentDate.getMinutes();
  const period = hour >= 12 ? "pm" : "am";

  hour %= 12;
  hour = hour || 12;

  const formattedDate = `${month} ${day}, ${year} ${hour}:${minute
    .toString()
    .padStart(2, "0")} ${period}`;

  return "Report generated on " + formattedDate;
}
