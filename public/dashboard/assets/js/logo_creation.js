$(document).ready(function () {
  let selectedFont;
  let selectedColors = [];

  $("#startFreeLogo").on("click", function () {
    moveSecondStep();
  });
  $("#businessType").on("keypress", function (event) {
    if (event.keyCode === 13 || event.which === 13) {
      moveThirdStep();
    }
  });
  $("#brand_name").on("keypress", function (event) {
    if (event.keyCode === 13 || event.which === 13) {
      moveSecondStep();
    }
  });
  $("#BackToStepOne").on("click", function () {
    backToStepOne();
  });

  $("#moveToThirdStep").on("click", function () {
    moveThirdStep();
  });
  $("#searchIconsInput").on("keypress", function (event) {
    if (event.keyCode === 13 || event.which === 13) {
      searchIcons($(this).val(), 100);
    }
  });

  $(document).on("click", ".icon-item", function () {
    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
      const selectedCount = $(".icon-item.selected").length;
      var selectedCountIcons = parseInt(selectedCount);
      $("#numberOfSelectedIcons").html(selectedCountIcons);
      return;
    }
    const selectedCount = $(".icon-item.selected").length;
    if (selectedCount >= 3) {
      return;
    }
    var selectedCountIcons = parseInt(selectedCount);
    selectedCountIcons++;
    $("#numberOfSelectedIcons").html(selectedCountIcons);
    $(this).addClass("selected");
  });

  $("#generateLogo").on("click", function () {
    $("#step5").addClass("animate__zoomOutLeft");
    setTimeout(() => {
      $("#step5").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step5").removeClass("animate__zoomOutLeft");
      $("#step6").addClass("animate__bounceInRight");
    }, 500);
    setTimeout(() => {
      $("#step6").addClass("active");
    }, 1100);

    generateLogo();
  });

  async function generateLogo() {
    console.log(selectedColors); // Logs the array of selected colors

    var iconsArray = [];

    // Extract all selected icons' src attributes
    var selectedIcons = $(".icon-item.selected");
    selectedIcons.find("img").each(function () {
      iconsArray.push($(this).attr("src"));
    });

    // Clear the finalLogoRow container
    $("#finalLogoRow").html("");

    // Loop through each icon and color combination
    for (const iconSrc of iconsArray) {
      // Wait for the icon to be fetched
      var icon = await fetchIconImage(downloadIconRoute, _token, iconSrc);

      if (!icon) {
        console.error("Failed to fetch icon:", iconSrc);
        continue; // Skip to the next iteration if the icon is not available
      }

      icon = icon
        .replace(
          /<svg/,
          '<svg viewBox="0 0 100 100" width="100%" height="200px"'
        ) // Set fixed width and height
        .replace(/<path/g, '<path fill="white" stroke="white"') // Ensure fill and stroke are added
        .replace(/fill="[^"]*"/g, 'fill="white"') // Replace any existing fill
        .replace(/stroke="[^"]*"/g, 'stroke="white"');

      var color1 = "#" + selectedColors[0]["color1"];
      var color2 = "#" + selectedColors[0]["color2"];
      var color3 = "#" + selectedColors[0]["color3"];
      var color4 = "#" + selectedColors[0]["color4"];

      var allColors = generateGradients(color1, color2, color3, color4);
      var brandName = $("#brand_name").val();

      if (allColors) {
        allColors.forEach((color) => {
          var logoString = `
          <div class="col-md-6 logoCard">
            <div class="card text-dark p-4 h-100 selectedPngFile" style="background: ${color};">
              <div class="card-body text-center">
                <div class='svgImage'>
                  ${icon}
                </div>
                <div>
                <h2 class="responsive_text ${selectedFont}">${brandName}</h2>
                </div>
              </div>
            </div>
            <div class="download_icons">
            <i class="fas fa-save uploadAndSave"></i>
            <i class="fas fa-download saveIconToDb"></i>
            </div>
          </div>`;
          $("#finalLogoRow").append(logoString);
        });
      }
    }
  }

  $(document).on("click", ".uploadAndSave", function(){
    var cardElement = $(this).closest(".logoCard").find(".selectedPngFile")[0];
    if (!cardElement) {
      console.error("No element found to capture.");
      return;
    }
    var btn = $(this);
    var btn_html = btn.html();
    html2canvas(cardElement)
      .then((canvas) => {
        btn.html("<i class='fa fa-spinner fa-spin'></i>")
        const imageData = canvas.toDataURL("image/png"); // Generate Base64 image data
        var url = downloadFileRoute;
        var businessType = $("#businessType").val();
        var brandName = $("#brand_name").val();
        $.ajax({
          url: url, // Your Laravel route
          method: "POST",
          data: {
            image: imageData,
            _token: _token,
            brandName:brandName,
            businessType:businessType
          },
          success: function (response) {
            btn.html(btn_html)
            location.href = logoDashboard;
          },
          error: function (error) {
            btn.html(btn_html);
          },
        });
      })
      .catch((error) => {
        console.error("Error generating canvas:", error);
      });


  });

  $(document).on("click", ".saveIconToDb", function () {
    var cardElement = $(this).closest(".logoCard").find(".selectedPngFile")[0]; // Select the actual DOM element

    if (!cardElement) {
      console.error("No element found to capture.");
      return;
    }

    html2canvas(cardElement)
      .then((canvas) => {
        const link = document.createElement("a");
        link.download = "logo.png"; // The downloaded file name
        link.href = canvas.toDataURL("image/png");
        link.click(); // Trigger the download
      })
      .catch((error) => {
        console.error("Error generating canvas:", error);
      });
  });

  $(document).on("click", ".selectedPngFile222", function () {
    const cardElement = this; // Get the clicked card element
    html2canvas(cardElement).then((canvas) => {
      const link = document.createElement("a");
      link.download = "logo.png"; // The downloaded file name
      link.href = canvas.toDataURL("image/png");
      link.click();
    });
  });

  async function fetchIconImage(downloadIconRoute, _token, icon) {
    try {
      const response = await $.post(downloadIconRoute, {
        _token: _token,
        url: icon,
      });

      // Check if the response contains `image`
      if (response && response.image) {
        return response.image; // Return the image URL or data
      } else {
        return false; // Return false if no image is found
      }
    } catch (error) {
      console.error("Error fetching the icon image:", error);
      return false; // Return false in case of an error
    }
  }

  function generateGradients(color1, color2, color3, color4) {
    return [
      `linear-gradient(45deg, ${color1}, ${color2})`,
      `linear-gradient(90deg, ${color2}, ${color3})`,
      `linear-gradient(135deg, ${color3}, ${color4})`,
      `linear-gradient(180deg, ${color1}, ${color4}, ${color2}, ${color3})`,
      color1,
      color2,
      color3,
      color4,
    ];
  }

  $(".selectColor").on("click", function () {
    var selectedColor1 = $(this).attr("data-color1");
    var selectedColor2 = $(this).attr("data-color2");
    var selectedColor3 = $(this).attr("data-color3");
    var selectedColor4 = $(this).attr("data-color4");
    selectedColors.push({
      color1: selectedColor1,
      color2: selectedColor2,
      color3: selectedColor3,
      color4: selectedColor4,
    });
    moveFifthStep();
    var queryString = $("#businessType").val();
    searchIcons(queryString, 100);
  });

  function searchIcons(search, limit) {
    var url = searchIconRoute;
    $.get(
      url,
      { _token: _token, search: search, limit: limit },
      function (data) {
        var icons = JSON.parse(data);
        if (icons.total > 0) {
          $("#svgIcons").html("");
          var totalIcons = icons.icons;
          totalIcons.forEach((icon) => {
            var iconSvgUrl = icon.icon_url;
            var svgString =
              '<div class="col-auto">\
                    <div class="icon-item">\
                        <img class="icon_image_box" src="' +
              icon.icon_url +
              '" style="width:100%;" />\
                    </div>\
                </div>';

            $("#svgIcons").append(svgString);
          });
        }
      }
    );
  }

  function moveFifthStep() {
    $("#step4").addClass("animate__zoomOutLeft");
    setTimeout(() => {
      $("#step4").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step4").removeClass("animate__zoomOutLeft");
      $("#step5").addClass("animate__bounceInRight");
    }, 500);
    setTimeout(() => {
      $("#step5").addClass("active");
    }, 1100);
  }

  function moveThirdStep() {
    $("#step2").addClass("animate__zoomOutLeft");
    setTimeout(() => {
      $("#step2").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step2").removeClass("animate__zoomOutLeft");
      $("#step3").addClass("animate__bounceInRight");
    }, 500);
    setTimeout(() => {
      $("#step3").addClass("active");
    }, 1100);
  }

  function backToStepOne() {
    $("#step2").addClass("animate__zoomOutRight");
    setTimeout(() => {
      $("#step2").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step2").removeClass("animate__zoomOutRight");
      $("#step1").addClass("animate__bounceInLeft");
    }, 500);
    setTimeout(() => {
      $("#step1").addClass("active");
    }, 1100);
  }

  $(".selectFont").on("click", function () {
    var font = $(this).attr("data-font");
    selectedFont = font;
    moveToFourthStep();
  });

  function moveToFourthStep() {
    $("#step3").addClass("animate__zoomOutLeft");
    setTimeout(() => {
      $("#step3").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step3").removeClass("animate__zoomOutLeft");
      $("#step4").addClass("animate__bounceInRight");
    }, 500);
    setTimeout(() => {
      $("#step4").addClass("active");
    }, 1100);
  }

  function moveSecondStep() {
    var brand = $("#brand_name").val();
    $(".brandName").html(brand);
    $("#step1").addClass("animate__zoomOutLeft");
    setTimeout(() => {
      $("#step1").removeClass("active");
    }, 500);
    setTimeout(() => {
      $("#step1").removeClass("animate__zoomOutLeft");
      $("#step2").addClass("animate__bounceInRight");
    }, 500);
    setTimeout(() => {
      $("#step2").addClass("active");
    }, 1100);
  }
});
