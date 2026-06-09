<div class="blockman_detail">
    <h2>{{ $lang->blockman }}</h2>

    {{-- Basic Info Section --}}
    <div class="blockman_section">
        <h3>{{ $lang->blockman_basic_info }}</h3>
        <table class="blockman_info_table">
            <tbody>
                <tr>
                    <th>{{ $lang->blockman_nickname }}</th>
                    <td>{{ $ban_record->nick_name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $lang->blockman_user_id }}</th>
                    <td>{{ $ban_record->user_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $lang->blockman_duration }}</th>
                    <td>{{ $duration_text }}</td>
                </tr>
                <tr>
                    <th>{{ $lang->blockman_ban_period }}</th>
                    <td>{{ $start_date_formatted }} ~ {{ $end_date_formatted ?: $lang->blockman_permanent }}</td>
                </tr>
                <tr>
                    <th>{{ $lang->blockman_status }}</th>
                    <td>
                        @if($ban_record->status === 'active')
                            <span class="blockman_badge blockman_badge--active">{{ $lang->blockman_status_active }}</span>
                        @elseif($ban_record->status === 'released')
                            <span class="blockman_badge blockman_badge--released">{{ $lang->blockman_status_released }}</span>
                        @else
                            <span class="blockman_badge blockman_badge--expired">{{ $lang->blockman_status_expired }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Reason Section --}}
    <div class="blockman_section">
        <h3>{{ $lang->blockman_ban_reason }}</h3>
        <table class="blockman_info_table">
            <tbody>
                <tr>
                    <th>{{ $lang->blockman_reason }}</th>
                    <td>{{ $ban_record->reason_tags ?? '-' }}</td>
                </tr>
                <tr>
                    <th>{{ $lang->blockman_reason_detail }}</th>
                    <td>{{ $ban_record->reason_detail ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
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
        <table class="blockman_table">
            <thead>
                <tr>
                    <th>{{ $lang->blockman_start_date }}</th>
                    <th>{{ $lang->blockman_duration }}</th>
                    <th>{{ $lang->blockman_reason }}</th>
                    <th>{{ $lang->blockman_status }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($member_history as $history)
                <tr>
                    <td>{{ $history->start_date ? substr($history->start_date, 0, 4) . '-' . substr($history->start_date, 4, 2) . '-' . substr($history->start_date, 6, 2) : '-' }}</td>
                    <td>{{ \Rhymix\Modules\Blockman\Models\BanRecord::formatDuration($history) }}</td>
                    <td>{{ $history->reason_tags ?? '-' }}</td>
                    <td>
                        @if($history->status === 'active')
                            <span class="blockman_badge blockman_badge--active">{{ $lang->blockman_status_active }}</span>
                        @elseif($history->status === 'released')
                            <span class="blockman_badge blockman_badge--released">{{ $lang->blockman_status_released }}</span>
                        @else
                            <span class="blockman_badge blockman_badge--expired">{{ $lang->blockman_status_expired }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="blockman_actions">
        <a href="{{ getUrl('', 'mid', $mid, 'act', 'dispBlockmanList') }}" class="blockman_btn">{{ $lang->blockman_back_to_list }}</a>
    </div>
</div>
