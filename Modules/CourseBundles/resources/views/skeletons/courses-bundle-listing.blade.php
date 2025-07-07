<div class="cr-bundles_wrap" >
<ul>
    @for($i = 0; $i < $total; $i++)
    <li>
        <div class="cr-bundles_item cr-bundles_item_skeleton">
            <figure></figure>
            <div class="cr-bundles_item_content">
                <div class="cr-bundles_coursename">
                    <span></span>
                    <div class="am-itemdropdown"></div>
                </div>
                <ul class="cr-bundles_item_footer">
                    <li>
                        <span></span> 
                        <em></em>
                    </li>
                    <li>
                        <span></span>
                        <em></em>
                    </li>
                </ul>
            </div>
            <div class="cr-bundle-price-container"></div>
        </div>
    </li>
    @endfor
</ul>
</div>