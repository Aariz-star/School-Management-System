window.onload = function() {
    const element = document.getElementById('receipt-content');
    if (!element) return;

    html2canvas(element, {
        scale: 2, // High resolution
        backgroundColor: "#ffffff"
    }).then(canvas => {
        const link = document.createElement('a');
        // Use filename from global variable or default
        const filename = window.receiptFilename || 'receipt.png';
        
        link.download = filename;
        link.href = canvas.toDataURL("image/png");
        link.click();
        
        document.body.innerHTML = '<div class="download-success"><h2>Receipt Downloaded!</h2><p>Check your downloads folder.</p><button onclick="window.close()" class="close-btn">Close Window</button></div>';
    });
};