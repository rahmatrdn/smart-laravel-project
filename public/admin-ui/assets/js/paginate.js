document.addEventListener('DOMContentLoaded', function() {
    function addNavigateAttribute() {
        var pageLinks = document.querySelectorAll('.page-link');
        pageLinks.forEach(function(link) {
            if (!link.hasAttribute('navigate')) {  
                link.setAttribute('navigate', '');
            }
        });
    }

    addNavigateAttribute();

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                addNavigateAttribute();
            }
        });
    });

    observer.observe(document.body, {
        childList: true, 
        subtree: true 
    });
});