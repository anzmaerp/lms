<!-- Skeleton start -->
<table class="am-table am-table_submitted">
    <thead>
        <tr>
            <th>{{ __('assignments::assignments.student') }}</th>
            <th>{{ __('assignments::assignments.obtained_marks') }}</th>
            <th>{{ __('assignments::assignments.submit_date') }}</th>
            <th>{{ __('assignments::assignments.status') }}</th>
            <th>{{ __('assignments::assignments.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @for($i = 0; $i < $total; $i++)
            <tr class="am-tr-skeleton">
                <td>    
                    <div class="am-user_info">
                        <figure></figure>
                        <div class="am-user_detail">
                            <span></span>
                            <em></em>
                        </div>
                    </div>
                </td>
                <td>
                    <span></span>
                </td>
                <td>
                    <span></span>
                </td>
                <td>
                    <div class="am-user_detail">
                        <span></span>
                        <em></em>
                    </div>
                </td>
                <td>
                    <span></span>
                </td>
                <td>
                    <div class="am-btn-skeleton"></div>
                </td>
            </tr>
        @endfor
    </tbody>
</table>
<!-- Skeleton End -->