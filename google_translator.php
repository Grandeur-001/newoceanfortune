<script>
    
    
    const targetElement = document.querySelector('#google_element .skiptranslate');

if (targetElement) {
    const removePoweredBy = () => {
        targetElement.childNodes.forEach(node => {
            if (node.nodeType === Node.TEXT_NODE && node.nodeValue.includes("Powered by")) {
                node.nodeValue = node.nodeValue.replace("Powered by", ""); // Remove "Powered by"
            }
        });
    };

    removePoweredBy();

    const observer = new MutationObserver(() => {
        removePoweredBy();
    });

    observer.observe(targetElement, { childList: true, subtree: true });
}


</script>
<style>
    #google_element{
        width: 100px;
    }
    select{
        width: 24px;
        height: 24px;
        border-radius: 100%;
        background: url("https://th.bing.com/th/id/OIP.9QEiX7dbsbPPs0CbvuSLJAHaHa?rs=1&pid=ImgDetMain");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        /* appearance: none; */
        /* -webkit-appearance: none; */
        scrollbar-width: none;
    }

    option{
        background-color: #222222;
        color: #ffffff;
        padding: 10px;
    }
    #text{
        display: none;
    }
    .VIpgJd-ZVi9od-l4eHX-hSRGPd{
        display: none;
    }

</style>
<div id="google_element"></div>
<script src="http://translate.google.com/translate_a/element.js?cb=loadGoogleTranslate"></script>
<script>
    function loadGoogleTranslate(){
        new google.translate.TranslateElement("google_element");
    }


</script>