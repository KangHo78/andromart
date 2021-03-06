@if($contents['home_achievement'] == true)
<div class="container">
    <div class="row counters counters-sm counters-text-dark py-4 my-5 justify-content-center">
        @foreach($homeAchievement as $row)
        @php $angka = (int)$row->subtitle @endphp
        <div class="col-sm-6 col-lg-3 mb-5 mb-lg-0">
            <div class="counter appear-animation" data-appear-animation="fadeInLeftShorter" data-appear-animation-delay="200">
                <i class="icons {{ $row->icon }} text-8 mb-3"></i>
                <strong class="font-weight-extra-bold mb-1" data-to="{{ $angka }}">0</strong>
                <label>{{ $row->title }}</label>
                <?php echo $row->description ?>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif