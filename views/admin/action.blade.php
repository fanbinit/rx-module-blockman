<div class="x_page-header">
    <h1>{{ $lang->blockman }} - {{ $lang->cmd_blockman_admin_action }}</h1>
</div>

@if(!empty($target_member))
<div class="x_panel">
    <h3>{{ $lang->blockman_target_member }}</h3>
    <p>닉네임: {{ $target_member->nick_name ?? '-' }} / 아이디: {{ $target_member->user_id ?? '-' }} (member_srl: {{ $target_member->member_srl }})</p>
</div>
@endif

<form action="./" method="post" class="x_form">
    <input type="hidden" name="module" value="admin" />
    <input type="hidden" name="act" value="procBlockmanAdminInsertBan" />
    <input type="hidden" name="member_srl" value="{{ Context::get('member_srl') }}" />
    <input type="hidden" name="document_srl" value="{{ Context::get('document_srl') }}" />
    <input type="hidden" name="comment_srl" value="{{ Context::get('comment_srl') }}" />

    <div class="x_form-group">
        <label>{{ $lang->blockman_select_ban_type }}</label>
        <select name="ban_type" class="x_input" id="ban_type_select">
            <option value="warning">{{ $lang->blockman_ban_type_warning }}</option>
            <option value="temporary">{{ $lang->blockman_ban_type_temporary }}</option>
            <option value="permanent">{{ $lang->blockman_ban_type_permanent }}</option>
        </select>
    </div>

    <div class="x_form-group" id="duration_group">
        <label>{{ $lang->blockman_select_duration }}</label>
        <select name="ban_duration" class="x_input">
            @foreach(($blockman_config->ban_duration_options ?? [1,5,30,180]) as $days)
            <option value="{{ $days }}">{{ $days }}일</option>
            @endforeach
        </select>
    </div>

    <div class="x_form-group">
        <label>{{ $lang->blockman_select_reason_tags }}</label>
        @foreach(($blockman_config->reason_tags ?? []) as $tag)
        <label class="x_checkbox">
            <input type="checkbox" name="reason_tags[]" value="{{ $tag }}" /> {{ $tag }}
        </label>
        @endforeach
    </div>

    <div class="x_form-group">
        <label>{{ $lang->blockman_reason_detail }}</label>
        <textarea name="reason_detail" class="x_input" rows="5" maxlength="500" required></textarea>
        <p class="x_help-block">1자 이상 500자 이하</p>
    </div>

    <div class="x_form-group x_form-group--button">
        <button type="submit" class="x_btn x_btn--danger">{{ $lang->blockman_execute_ban }}</button>
    </div>
</form>

<script>
document.getElementById('ban_type_select').addEventListener('change', function() {
    document.getElementById('duration_group').style.display = this.value === 'temporary' ? '' : 'none';
});
document.getElementById('ban_type_select').dispatchEvent(new Event('change'));
</script>
