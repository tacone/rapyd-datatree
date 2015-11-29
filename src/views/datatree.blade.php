

<div class="datatree-w">
{{ $dg->open }}
    <?php $rows = $dg->rows ?>
    @if ($rows)
        @include('datatree::item')
    @else
        <div class="datatree-empty">
            No items
        </div>
    @endif

{{ $dg->close }}
    @include('rapyd::toolbar', array('label'=>$label, 'buttons_right'=>$buttons['BR'], 'buttons_left'=>$buttons['BL']))

</div>
