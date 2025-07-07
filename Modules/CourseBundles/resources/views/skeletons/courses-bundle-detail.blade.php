<div class="cr-bundle-detail_includecourses">
<ul>
    @for($i = 0; $i < $total; $i++)
        <div class="cr-bundle-detail_courseitem cr-bundle-detail_skeleton">
            <figure></figure>
            <div class="cr-bundle-course-detail">
                <div class="cr-bundle-course-detail_content">
                    <span></span>
                    <ul>
                        <li>
                            <em></em>
                            <span></span>
                        </li>
                        <li>
                            <em></em>
                            <span></span>
                        </li>
                        <li>
                            <em></em>
                            <span></span>
                        </li>
                        <li>
                            <em></em>
                            <span></span>
                        </li>
                        <li>
                            <em></em>
                            <span></span>
                        </li>
                    </ul>
                    <div class="cr-description-skeleton">
                        <span></span>
                        <span></span>
                    </div>
                </div>
                @if(isPaidSystem())
                    <div class="cr-bundle-course-detail_price">
                        <span></span>
                    </div>
                @endif
            </div>
        </div>
    @endfor
</div>