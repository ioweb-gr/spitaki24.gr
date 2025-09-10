document.addEventListener('DOMContentLoaded', function() {
  const dialogs = document.querySelectorAll('dialog.shoptimizer-modal');
  var shoptimizerFElements = getFocusableElements();
  var shoptimizerFFElement = shoptimizerFElements[0];
  var shoptimizerLFElement = shoptimizerFElements[shoptimizerFElements.length - 1];
  
  document.addEventListener('click', event => {
    const shoptimizertrigger = event.target.dataset.trigger;
    if (shoptimizertrigger) {
      const modalId = shoptimizertrigger;
      const modalElement = document.querySelector(`[data-shoptimizermodal-id="${modalId}"]`);
      if (modalElement) {
        closeAllDialogs();
        if (modalId === 'searchToggle') {
          modalElement.show();
          updateFocusableElements();
          trapSearchToggleModal(modalElement);
        } else {
          modalElement.showModal();
        }
      }
    }
  });
  
  dialogs.forEach(shoptimizerdialog => {
    shoptimizerdialog.addEventListener('click', function(event) {
      if (event.target === shoptimizerdialog) {
        closeDialog(shoptimizerdialog);
      }
      if (event.target.closest('.shoptimizer-modal--button_close')) {
        event.preventDefault();
        closeDialog(shoptimizerdialog);
      }
    });
    
    // Add keydown event listener for ESC key
    shoptimizerdialog.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeDialog(shoptimizerdialog);
      }
    });
  });
  
  function closeAllDialogs() {
    dialogs.forEach(dialog => {
      if (dialog.open) {
        dialog.close();
      }
    });
  }
  
  function closeDialog(dialog) {
    dialog.close();
  }
  
  function getFocusableElements() {
    var modalElm = document.querySelector('[data-shoptimizermodal-id="searchToggle"]');
    if (modalElm) {
      return modalElm.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
    } else {
      return new Array();
    }
  }
  
  function updateFocusableElements() {
    shoptimizerFElements = getFocusableElements();
    shoptimizerFFElement = shoptimizerFElements[0];
    shoptimizerLFElement = shoptimizerFElements[shoptimizerFElements.length - 1];
  }
  
  function trapSearchToggleModal(element) {
    shoptimizerFFElement.focus();
    
    element.addEventListener('keydown', function(e) {
      let isTabPressed = e.key === 'Tab' || e.keyCode === 9;
      if (!isTabPressed) {
        return;
      }
      if (e.shiftKey) { 
        if (document.activeElement === shoptimizerFFElement) {
          shoptimizerLFElement.focus();
          e.preventDefault();
        }
      } else { 
        if (document.activeElement === shoptimizerLFElement) { 
          shoptimizerFFElement.focus(); 
          e.preventDefault();
        }
      }
    });
  }
  
  var modalContent = document.querySelector('[data-shoptimizermodal-id="searchToggle"]');
  if (modalContent) {
    var modalObserver = new MutationObserver(() => {
      updateFocusableElements();
    });
    modalObserver.observe(modalContent, { childList: true, subtree: true });
  }
});