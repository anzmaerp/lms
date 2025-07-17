<div class="cr-bundles_wrap" >
<ul>
    @for($i = 0; $i < $total; $i++)
    <li>
        <div class="cr-bundles_item cr-bundles_item_skeleton">
            <figure></figure>
            <div class="cr-bundles_item_content">
                <div class="cr-bundles_user">
                    <figure></figure>
                    <span></span>
                    <span></span>
                </div>
                <div class="cr-bundles_coursetitle">
                    <span></span>
                </div>
                <span></span>
                <div class="cr-bundle-price-container"></div>
            </div>
        </div>
    </li>
    @endfor
</ul>
</div>