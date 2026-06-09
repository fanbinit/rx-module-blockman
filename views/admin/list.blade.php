<div class="x_page-header">
    <h1>{{ $lang->blockman }} - {{ $lang->cmd_blockman_admin_list }}</h1>
</div>

<div class="x_list-header">
    <form action="./" method="get" class="x_form--inline">
        <input type="hidden" name="module" value="admin" />
        <input type="hidden" name="act" value="dispBlockmanAdminList" />
        <select name="search_status" class="x_input">
            <option value="">전체</option>
            <option value="active" @if($search_status === 'active') selected @endif>{{ $lang->blockman_status_active }}</option>
            <option value="released" @if($search_status === 'released') selected @endif>{{ $lang->blockman_status_released }}</option>
            <option value="expired" @if($search_status === 'expired') selected @endif>{{ $lang->blockman_status_expired }}</option>
        </select>
        <input type="text" name="search_user_id" value="{{ $search_user_id ?? '' }}" class="x_input" placeholder="{{ $lang->blockman_search_placeholder }}" />
        <button type="submit" class="x_btn">{{ $lang->blockman_search }}</button>
    </form>
</div>

@if(!empty($ban_list))
<table class="x_table">
    <thead>
        <tr>
            <th>{{ $lang->blockman_nickname }}</th>
            <th>{{ $lang->blockman_user_id }}</th>
            <th>{{ $lang->blockman_start_date }}</th>
            <th>{{ $lang->blockman_duration }}</th>
            <th>{{ $lang->blockman_reason }}</th>
            <th>상태</th>
            <th>관리</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ban_list as $record)
        <tr>
            <td>{{ $record->nick_name ?? '-' }}</td>
            <td>{{ $record->user_id ?? '-' }}</td>
            <td>{{ substr($record->start_date, 0, 4) }}-{{ substr($record->start_date, 4, 2) }}-{{ substr($record->start_date, 6, 2) }}</td>
            <td>{{ \Rhymix\Modules\Blockman\Models\BanRecord::formatDuration($record) }}</td>
            <td>{{ $record->reason_tags }}</td>
            <td>
                @if($record->status === 'active')
                    <span class="x_label x_label--danger">{{ $lang->blockman_status_active }}</span>
                @elseif($record->status === 'released')
                    <span class="x_label x_label--success">{{ $lang->blockman_status_released }}</span>
                @else
                    <span class="x_label">{{ $lang->blockman_status_expired }}</span>
                @endif
            </td>
            <td>
                @if($record->status === 'active')
                <button type="button" class="x_btn x_btn--sm" onclick="document.getElementById('release_form_{{ $record->ban_record_srl }}').style.display='block'">해제</button>
                <form id="release_form_{{ $record->ban_record_srl }}" action="./" method="post" style="display:none; margin-top:5px;">
                    <input type="hidden" name="module" value="admin" />
                    <input type="hidden" name="act" value="procBlockmanAdminReleaseBan" />
                    <input type="hidden" name="ban_record_srl" value="{{ $record->ban_record_srl }}" />
                    <input type="text" name="released_reason" placeholder="{{ $lang->blockman_release_reason }}" class="x_input x_input--sm" required />
                    <button type="submit" class="x_btn x_btn--sm x_btn--danger">확인</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($page_navigation)
<div class="x_pagination">
    @include('common.paginate', ['page_navigation' => $page_navigation])
</div>
@endif

@else
<p class="x_empty-message">{{ $lang->blockman_no_records }}</p>
@endif
