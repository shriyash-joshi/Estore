<div class="container">
    <br />
    <div class="well well-lg">
        <h2 class="text-center">Please wait while the page loads...</h2>
    </div>
</div>

<script>
    $(function () {
        parent.$.fn.colorbox.resize({height: '99%', width: '100%'});
        window.location = '<?php echo $testUrl; ?>';
    });
</script>