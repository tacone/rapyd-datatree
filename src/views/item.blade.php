<ol class="datatree-list">
    @foreach ($rows as $row)
        <li{{ $row->buildAttributes() }}>
            <div class="datatree-handle">
                @foreach ($row->cells as $cell)
                    <span{{ $cell->buildAttributes() }}>{{ $cell->value }}</span>
                @endforeach
            </div>
            @if ($row->children)
                <?php $rows = $row->children; ?>
                @include('datatree::item')
            @endif

        </li>
    @endforeach
</ol>