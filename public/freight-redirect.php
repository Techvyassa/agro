<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Freight Calculator</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div style="text-align: center; margin-top: 100px;">
        <h2>Redirecting to Freight Calculator...</h2>
        <p>Please wait while we prepare your shipment data.</p>
        <div class="spinner" style="margin: 20px auto; width: 50px; height: 50px; border: 5px solid #f3f3f3; border-top: 5px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;"></div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Inject POST data as hidden fields for JS -->
    <input type="hidden" id="so_no" value="<?php echo isset($_POST['so_no']) ? htmlspecialchars($_POST['so_no']) : ''; ?>">
    <input type="hidden" id="boxes" value="<?php echo isset($_POST['boxes']) ? htmlspecialchars($_POST['boxes']) : ''; ?>">

    <script>
        $(document).ready(function() {
            // Get data from hidden fields injected by PHP
            const soNo = document.getElementById('so_no').value;
            const boxesJson = document.getElementById('boxes').value;

            if (!soNo || !boxesJson) {
                alert('Missing required shipment data');
                window.location.href = '/freight-calculator';
                return;
            }

            try {
                // Parse the boxes data
                const boxes = JSON.parse(decodeURIComponent(boxesJson));
                // For debugging
                console.log('Boxes data:', boxes);
                // Create a form to post data to freight.php
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/freight.php';
                // Calculate total weight
                let totalWeight = 0;
                boxes.forEach(box => {
                    totalWeight += parseFloat(box.weight) || 0;
                });
                // Format dimensions for all boxes
                if (boxes.length > 0) {
                    const allDimensions = [];
                    const allWeights = [];
                    boxes.forEach(box => {
                        let dimensions = [15, 15, 15];
                        if (box.dimension && box.dimension.includes('x')) {
                            const dimParts = box.dimension.split('x').map(d => parseInt(d.trim()));
                            if (dimParts.length >= 3) {
                                dimensions = dimParts;
                            }
                        }
                        allDimensions.push(`${dimensions[0]}x${dimensions[1]}x${dimensions[2]}`);
                        let boxWeight = parseFloat(box.weight) || 5;
                        allWeights.push(boxWeight.toFixed(2));
                    });
                    const dimensionsStr = allDimensions.join(',');
                    const weightsStr = allWeights.join(',');
                    appendInput(form, 'dimensions', dimensionsStr);
                    appendInput(form, 'boxWeights', weightsStr);
                    appendInput(form, 'boxCount', boxes.length);
                    appendInput(form, 'totalWeight', Math.round(totalWeight * 1000));
                    if (boxes.length > 0) {
                        const firstBox = boxes[0];
                        let dimensions = [15, 15, 15];
                        if (firstBox.dimension && firstBox.dimension.includes('x')) {
                            const dimParts = firstBox.dimension.split('x').map(d => parseInt(d.trim()));
                            if (dimParts.length >= 3) {
                                dimensions = dimParts;
                            }
                        }
                        appendInput(form, 'length', dimensions[0]);
                        appendInput(form, 'width', dimensions[1]);
                        appendInput(form, 'height', dimensions[2]);
                        appendInput(form, 'deadWeight', parseFloat(firstBox.weight) || 5);
                    }
                    appendInput(form, 'boxData', JSON.stringify(boxes));
                }
                document.body.appendChild(form);
                setTimeout(() => {
                    form.submit();
                }, 1000);
            } catch (e) {
                console.error('Error processing shipment data:', e);
                alert('Error processing shipment data. Redirecting to freight calculator.');
                setTimeout(() => {
                    window.location.href = '/freight-calculator';
                }, 1500);
            }
            function appendInput(form, name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }
        });
    </script>
</body>
</html> 