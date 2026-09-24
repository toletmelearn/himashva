<script>
    // Reserved for future admin-panel behavior. Intentionally empty for now.
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.fi-wi-stats-overview-stat-value').forEach(function(el) {
        var text = el.textContent.trim();
        var match = text.match(/[\d,]+/);
        if (!match) return;
        var target = parseInt(match[0].replace(/,/g, ''));
        var prefix = text.substring(0, text.indexOf(match[0]));
        var suffix = text.substring(text.indexOf(match[0]) + match[0].length);
        var start = performance.now();
        var duration = 1200;
        function animate(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = prefix + Math.round(target * eased).toLocaleString('en-IN') + suffix;
            if (progress < 1) requestAnimationFrame(animate);
        }
        requestAnimationFrame(animate);
    });
});
</script>
