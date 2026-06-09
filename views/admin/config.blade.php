@include('_header')

<form action="./" method="post" class="x_form">
    <input type="hidden" name="module" value="admin" />
    <input type="hidden" name="act" value="procBlockmanAdminSaveConfig" />
    <input type="hidden" name="success_return_url" value="{{ getUrl('', 'module', 'admin', 'act', 'dispBlockmanAdminConfig') }}" />

    <div class="x_form-group">
        <label for="appeal_board_mid">{{ $lang->blockman_config_appeal_board }}</label>
        <input type="text" id="appeal_board_mid" name="appeal_board_mid" value="{{ $blockman_config->appeal_board_mid ?? '' }}" class="x_input" placeholder="mid" />
    </div>

    <div class="x_form-group">
        <label>{{ $lang->blockman_config_reason_tags }}</label>
        <textarea name="reason_tags" class="x_input" rows="5" placeholder="태그를 콤마(,)로 구분하여 입력">{{ is_array($blockman_config->reason_tags ?? []) ? implode(',', $blockman_config->reason_tags) : ($blockman_config->reason_tags ?? '') }}</textarea>
        <p class="x_help-block">콤마(,)로 구분. 최소 1개 ~ 최대 20개, 각 30자 이하.</p>
    </div>

    <div class="x_form-group">
        <label>{{ $lang->blockman_config_ban_duration_options }}</label>
        <input type="text" name="ban_duration_options" value="{{ is_array($blockman_config->ban_duration_options ?? []) ? implode(',', $blockman_config->ban_duration_options) : ($blockman_config->ban_duration_options ?? '') }}" class="x_input" placeholder="1,5,30,180" />
        <p class="x_help-block">콤마(,)로 구분. 1~3650 범위, 최소 1개 ~ 최대 10개.</p>
    </div>

    <div class="x_form-group">
        <label>{{ $lang->blockman_config_list_access }}</label>
        <select name="list_access_level" class="x_input">
            <option value="member" @if(($blockman_config->list_access_level ?? 'member') === 'member') selected @endif>{{ $lang->blockman_config_list_access_member }}</option>
            <option value="manager" @if(($blockman_config->list_access_level ?? 'member') === 'manager') selected @endif>{{ $lang->blockman_config_list_access_manager }}</option>
        </select>
    </div>

    <div class="x_form-group x_form-group--button">
        <button type="submit" class="x_btn x_btn--primary">{{ $lang->cmd_save }}</button>
    </div>
</form>
