<div class="blockman_list">
    <h2>{{ $lang->blockman }}</h2>

    <div class="blockman_search">
        <form action="./" method="get" class="blockman_search_form">
            <input type="hidden" name="mid" value="{{ $mid }}" />
            <input type="hidden" name="act" value="dispBlockmanList" />
            <input type="text" name="search_user_id" value="{{ $search_user_id ?? '' }}" placeholder="{{ $lang->blockman_search_placeholder }}" class="blockman_input" />
            <button type="submit" class="blockman_btn">{{ $lang->blockman_search }}</button>
        </form>
    </div>

    @if(!empty($ban_list))
    <ul class="blockman_card_list">
        @foreach($ban_list as $record)
        <li class="blockman_card">
            <a href="{{ getUrl('', 'mid', $mid, 'act', 'dispBlockmanDetail', 'ban_record_srl', $record->ban_record_srl) }}">
                <div class="blockman_card_header">
                    <strong>{{ $record->nick_name ?? '-' }}</strong>
                    @if($record->status === 'active')
                        <span class="blockman_badge blockman_badge--active">{{ $lang->blockman_status_active }}</span>
                    @elseif($record->status === 'released')
                        <span class="blockman_badge blockman_badge--released">{{ $lang->blockman_status_released }}</span>
                    @else
                        <span class="blockman_badge blockman_badge--expired">{{ $lang->blockman_status_expired }}</span>
                    @endif
                </div>
                <div class="blockman_card_body">
                    <span>{{ $record->user_id ?? '-' }}</span>
                    <span>{{ substr($record->start_date, 0, 4) }}-{{ substr($record->start_date, 4, 2) }}-{{ substr($record->start_date, 6, 2) }}</span>
                    <span>{{ \Rhymix\Modules\Blockman\Models\BanRecord::formatDuration($record) }}</span>
                </div>
                <div class="blockman_card_footer">
                    {{ $record->reason_tags }}
                </div>
            </a>
        </li>
        @endforeach
    </ul>

    @if($page_navigation)
    <div class="blockman_pagination">
        @include('common.paginate', ['page_navigation' => $page_navigation])
    </div>
    @endif

    @else
    <p class="blockman_empty">{{ $lang->blockman_no_records }}</p>
    @endif
</div>
