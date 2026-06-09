<div class="blockman_detail">
    <h2>{{ $lang->blockman }}</h2>

    {{-- Basic Info Section --}}
    <div class="blockman_section">
        <h3>{{ $lang->blockman_basic_info }}</h3>
        <dl class="blockman_dl">
            <dt>{{ $lang->blockman_nickname }}</dt>
            <dd>{{ $ban_record->nick_name ?? '-' }}</dd>

            <dt>{{ $lang->blockman_user_id }}</dt>
            <dd>{{ $ban_record->user_id ?? '-' }}</dd>

            <dt>{{ $lang->blockman_duration }}</dt>
            <dd>{{ $duration_text }}</dd>

            <dt>{{ $lang->blockman_ban_period }}</dt>
            <dd>{{ $start_date_formatted }} ~ {{ $end_date_formatted ?: $lang->blockman_permanent }}</dd>

            <dt>{{ $lang->blockman_status }}</dt>
            <dd>
                @if($ban_record->status === 'active')
                    <span class="blockman_badge blockman_badge--active">{{ $lang->blockman_status_active }}</span>
                @elseif($ban_record->status === 'released')
                    <span class="blockman_badge blockman_badge--released">{{ $lang->blockman_status_released }}</span>
                @else
                    <span class="blockman_badge blockman_badge--expired">{{ $lang->blockman_status_expired }}</span>
                @endif
            </dd>
        </dl>
    </div>

    {{-- Reason Section --}}
    <div class="blockman_section">
        <h3>{{ $lang->blockman_ban_reason }}</h3>
        <dl class="blockman_dl">
            <dt>{{ $lang->blockman_reason }}</dt>
            <dd>{{ $ban_record->reason_tags ?? '-' }}</dd>

            <dt>{{ $lang->blockman_reason_detail }}</dt>
            <dd>{{ $ban_record->reason_detail ?? '-' }}</dd>
        </dl>
    </div>

    {{-- Appeal Section --}}
    @if(!empty($appeal_board_mid) && $list_access_level === 'member')
    <div class="blockman_section">
        <h3>{{ $lang->blockman_appeal_info }}</h3>
        <p>{{ $lang->blockman_appeal_guide }}</p>
        @if($appeal_window_open)
            <a href="{{ getUrl('', 'mid', $appeal_board_mid) }}" class="blockman_btn">{{ $lang->blockman_appeal_info }}</a>
        @else
            <p class="blockman_notice">{{ $lang->blockman_appeal_not_available }}</p>
        @endif
    </div>
    @endif

    {{-- Member History Section --}}
    @if(!empty($member_history))
    <div class="blockman_section">
        <h3>{{ $lang->blockman_member_history }}</h3>
        <ul class="blockman_history_list">
            @foreach($member_history as $history)
            <li class="blockman_history_item">
                <div class="blockman_history_header">
                    <span>{{ $history->start_date ? substr($history->start_date, 0, 4) . '-' . substr($history->start_date, 4, 2) . '-' . substr($history->start_date, 6, 2) : '-' }}</span>
                    @if($history->status === 'active')
                        <span class="blockman_badge blockman_badge--active">{{ $lang->blockman_status_active }}</span>
                    @elseif($history->status === 'released')
                        <span class="blockman_badge blockman_badge--released">{{ $lang->blockman_status_released }}</span>
                    @else
                        <span class="blockman_badge blockman_badge--expired">{{ $lang->blockman_status_expired }}</span>
                    @endif
                </div>
                <div class="blockman_history_body">
                    <span>{{ \Rhymix\Modules\Blockman\Models\BanRecord::formatDuration($history) }}</span>
                    <span>{{ $history->reason_tags ?? '-' }}</span>
                </div>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="blockman_actions">
        <a href="{{ getUrl('', 'mid', $mid, 'act', 'dispBlockmanList') }}" class="blockman_btn">{{ $lang->blockman_back_to_list }}</a>
    </div>
</div>
