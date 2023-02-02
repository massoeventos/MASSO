<form id="form" action="{{ $url }}" method="post">
    <input type="hidden" name="token_ws" value="{{ $token }}">
</form>

<script>
    
    document.getElementById("form").submit();
        
</script>