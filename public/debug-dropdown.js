// Debug helper for dropdown profile
// Paste this in browser console after page load
(function(){
  var btn = document.getElementById('profileDropdownBtn');
  var dropdown = document.getElementById('profileDropdown');
  if (!btn) { console.error('profileDropdownBtn NOT FOUND'); return; }
  if (!dropdown) { console.error('profileDropdown NOT FOUND'); return; }
  btn.style.border = '2px solid red';
  dropdown.style.border = '2px solid blue';
  btn.addEventListener('click', function() {
    dropdown.classList.toggle('hidden');
    console.log('Dropdown toggled. Now hidden:', dropdown.classList.contains('hidden'));
  });
  console.log('Debug script injected. Click the profile image.');
})();
