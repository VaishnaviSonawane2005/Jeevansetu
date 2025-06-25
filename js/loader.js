// Hide loader after full page load
window.addEventListener('load', () => {
  const loader = document.getElementById('jsLoader');
  if (loader) loader.style.display = 'none';
});

// Show loader on any anchor or button click
document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById('jsLoader');

  // Add click listeners to all links and buttons
  document.querySelectorAll('a, button').forEach(el => {
    el.addEventListener('click', e => {
      // Ignore links that open in new tab or anchor links
      const target = el.getAttribute('target');
      const href = el.getAttribute('href');
      if (target === '_blank' || (href && href.startsWith('#'))) return;

      if (loader) loader.style.display = 'flex';
    });
  });
});
// Optional delay if needed for better effect
setTimeout(() => {
  if (loader) loader.style.display = 'none';
}, 1200); // 1.2 seconds
