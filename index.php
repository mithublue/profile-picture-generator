<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Profile Picture Generator with Layers</title>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<h1 class="text-2xl font-bold mb-6 text-center">Enhanced Profile Picture Generator with Layers</h1>
<div class="flex space-x-6">
	<!-- Canvas and Layers Section -->
	<div class="w-2/3">
		<!-- Canvas Container -->
		<div class="bg-white shadow-md p-4 mb-6">
			<h2 class="text-xl font-semibold mb-4">Canvas</h2>
			<div id="canvasContainer" class="flex justify-center">
				<canvas id="canvas" width="400" height="400" class="border border-gray-300"></canvas>
			</div>
		</div>

		<!-- Layers Panel -->
		<div class="bg-white shadow-md p-4">
			<h3 class="text-xl font-semibold mb-4">Layers</h3>
			<div id="layersList" class="space-y-2"></div>
		</div>
	</div>

	<!-- Controls Section -->
	<div class="w-1/3 bg-white shadow-md p-6">
		<!-- Image Upload Section -->
		<div class="mb-6">
			<label for="uploadImage" class="block text-sm font-medium text-gray-700 mb-2">Upload Profile Image:</label>
			<input type="file" id="uploadImage" class="border border-gray-300 rounded-md p-2 w-full">
			<button id="deleteImage" class="mt-2 bg-red-500 text-white px-4 py-2 rounded">Delete Image</button>
		</div>

		<!-- Badge/Frame Upload -->
		<div class="mb-6">
			<label for="uploadBadge" class="block text-sm font-medium text-gray-700 mb-2">Upload Badge/Frame:</label>
			<input type="file" id="uploadBadge" class="border border-gray-300 rounded-md p-2 w-full">
		</div>

		<!-- Text Input Section -->
		<div class="mb-6">
			<label for="textInput" class="block text-sm font-medium text-gray-700 mb-2">Add Text:</label>
			<input type="text" id="textInput" placeholder="Enter text for profile picture" class="border border-gray-300 rounded-md p-2 w-full">
			<button id="addText" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">Add Text</button>
		</div>

		<!-- Text Styling Section -->
		<div class="mb-6">
			<label for="fontSize" class="block text-sm font-medium text-gray-700 mb-2">Font Size:</label>
			<input type="number" id="fontSize" min="10" max="100" value="30" class="border border-gray-300 rounded-md p-2 w-full">

			<label for="fontColor" class="block text-sm font-medium text-gray-700 mt-4 mb-2">Font Color:</label>
			<input type="color" id="fontColor" value="#ffffff" class="border border-gray-300 rounded-md p-2 w-full">

			<label for="fontFamily" class="block text-sm font-medium text-gray-700 mt-4 mb-2">Font Family:</label>
			<select id="fontFamily" class="border border-gray-300 rounded-md p-2 w-full">
				<option value="Arial">Arial</option>
				<option value="Georgia">Georgia</option>
				<option value="Courier">Courier</option>
				<option value="Verdana">Verdana</option>
			</select>
		</div>

		<!-- Image Control Section -->
		<div class="mb-6">
			<label for="rotateImage" class="block text-sm font-medium text-gray-700 mb-2">Rotate Image:</label>
			<input type="input" id="rotateImageRange" min="0" max="360" value="0" class="w-full">
		</div>

		<div class="mb-6">
			<label for="scaleImage" class="block text-sm font-medium text-gray-700 mb-2">Scale Image:</label>
			<input type="range" id="scaleImageRange" min="0.1" max="2" step="0.1" value="1" class="w-full">
		</div>

		<!-- Download Button -->
		<div class="mb-6">
			<button id="download" class="bg-green-500 text-white px-4 py-2 rounded w-full">Download Profile Picture</button>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		// Initialize Fabric.js canvas
		const canvas = new fabric.Canvas('canvas', {
			width: 400,
			height: 400
		});
		let uploadedImage = null;

		// Image Upload
		$('#uploadImage').change(function (e) {
			const reader = new FileReader();
			reader.onload = function (event) {
				const imgObj = new Image();
				imgObj.src = event.target.result;
				imgObj.onload = function () {
					if (uploadedImage) {
						canvas.remove(uploadedImage);  // Remove previous image
					}
					uploadedImage = new fabric.Image(imgObj);
					uploadedImage.scaleToWidth(400);  // Scale image to fit the canvas
					canvas.add(uploadedImage);
					canvas.sendToBack(uploadedImage);
					updateLayersList();
					$('#uploadImage').val('');  // Clear the input
				};
			};
			reader.readAsDataURL(e.target.files[0]);
		});

		// Badge or Frame Image Upload
		$('#uploadBadge').change(function (e) {
			const reader = new FileReader();
			reader.onload = function (event) {
				const imgObj = new Image();
				imgObj.src = event.target.result;
				imgObj.onload = function () {
					const badgeImage = new fabric.Image(imgObj);
					badgeImage.scaleToWidth(100); // Adjust the size as needed
					badgeImage.set({
						left: 150, // Adjust the initial positioning if needed
						top: 150,  // Adjust the initial positioning if needed
						selectable: true
					});
					canvas.add(badgeImage);
					canvas.setActiveObject(badgeImage);  // Set the badge as the active object
					updateLayersList();
					$('#uploadBadge').val('');  // Clear the badge input after adding
				};
			};
			reader.readAsDataURL(e.target.files[0]);
		});

		// Delete Image
		$('#deleteImage').click(function () {
			if (uploadedImage) {
				canvas.remove(uploadedImage);
				uploadedImage = null;
				updateLayersList();
			}
		});

		// Add Text
		$('#addText').click(function () {
			const textInput = $('#textInput').val();
			const fontSize = $('#fontSize').val();
			const fontColor = $('#fontColor').val();
			const fontFamily = $('#fontFamily').val();

			if (textInput) {
				const text = new fabric.Text(textInput, {
					left: 100,
					top: 350,
					fontSize: fontSize,
					fill: fontColor,
					fontFamily: fontFamily,
					fontWeight: 'bold',
					shadow: 'rgba(0,0,0,0.3) 2px 2px 5px',
					editable: true
				});
				canvas.add(text);
				canvas.setActiveObject(text);  // Set the text as the active object
				updateLayersList();
				$('#textInput').val('');  // Clear the text input
			}
		});

		// Update font size, color, and family for active text
		$('#fontSize').on('input', updateTextStyles);
		$('#fontColor').on('input', updateTextStyles);
		$('#fontFamily').change(updateTextStyles);

		function updateTextStyles() {
			if (canvas.getActiveObject() && canvas.getActiveObject().type === 'text') {
				const fontSize = $('#fontSize').val();
				const fontColor = $('#fontColor').val();
				const fontFamily = $('#fontFamily').val();

				const activeText = canvas.getActiveObject();
				activeText.set({
					fontSize: fontSize,
					fill: fontColor,
					fontFamily: fontFamily
				});
				canvas.renderAll();
			}
		}

		// Rotate and Scale Image
		$('#rotateImageRange').on('input', function () {
			const value = $(this).val();
			if (uploadedImage) {
				uploadedImage.set('angle', parseInt(value));
				canvas.renderAll();
			}
		});

		$('#scaleImageRange').on('input', function () {
			const value = $(this).val();
			if (uploadedImage) {
				uploadedImage.scale(parseFloat(value));
				canvas.renderAll();
			}
		});

		// Update layers list
		function updateLayersList() {
			const layers = canvas.getObjects();
			$('#layersList').empty();

			layers.forEach((layer, index) => {
				const layerItem = $('<div></div>').addClass('p-2 bg-gray-100 rounded-md cursor-pointer');
				layerItem.text(layer.type === 'text' ? 'Text Layer' : 'Image Layer');
				layerItem.click(function () {
					canvas.setActiveObject(layer);  // Set the clicked layer as active
					canvas.renderAll();
				});
				$('#layersList').append(layerItem);
			});
		}

		// Download Image
		$('#download').click(function () {
			const dataURL = canvas.toDataURL();
			const link = document.createElement('a');
			link.href = dataURL;
			link.download = 'profile_picture.png';
			link.click();
		});
	});
</script>

</body>
</html>
