<header id="header" class="header-transparent header-semi-transparent header-semi-transparent-dark header-effect-shrink" data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'shrink', 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': true, 'stickyChangeLogo': true, 'stickyStartAt': 30, 'stickyHeaderContainerHeight': 70}">
	<div class="header-body border-top-0 bg-dark box-shadow-none">
		<div class="header-container container">
			<div class="header-row">
				<div class="header-column">
					<div class="header-row">
						<div class="header-logo">
							<a href="/">
								<img alt="Porto" src="{{ asset('assetsfrontend/img/logo_nonbg.png') }}" style="height: 70px; object-fit: cover;">
							</a>
						</div>
					</div>
				</div>
				<div class="header-column justify-content-end">
					<div class="header-row">
						<div class="header-nav header-nav-links header-nav-dropdowns-dark header-nav-light-text order-2 order-lg-1">
							<div class="header-nav-main header-nav-main-mobile-dark header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-effect-2 header-nav-main-sub-effect-1">
								<nav class="collapse">
									<ul class="nav nav-pills" id="mainNav">
										@php
											$menuActive = app()->view->getSections()['menu-active'];
										@endphp
										<li>
											@if($menuActive == 'home')
												<a class="nav-link active" href="{{ route('frontendHome') }}">Home</a>
											@else
												<a class="nav-link" href="{{ route('frontendHome') }}">Home</a>
											@endif
										</li>
										<li>
											@if($menuActive == 'product')
												<a class="nav-link active" href="{{ route('frontendProduct') }}">Product</a>
											@else
												<a class="nav-link" href="{{ route('frontendProduct') }}">Product</a>
											@endif
										</li>
										<li>
											@if($menuActive == 'services')
												<a class="nav-link active" href="{{ route('frontendServices') }}">Services</a>
											@else
												<a class="nav-link" href="{{ route('frontendServices') }}">Services</a>
											@endif

										</li>
										<!-- <li>
											@if($menuActive == 'work')
												<a class="nav-link active" href="{{ route('frontendWork') }}">Work</a>
											@else
												<a class="nav-link" href="{{ route('frontendWork') }}">Work</a>
											@endif
										</li> -->
										<li>
											@if($menuActive == 'contact')
												<a class="nav-link active" href="{{ route('frontendContact') }}">Contact</a>
											@else
												<a class="nav-link" href="{{ route('frontendContact') }}">Contact</a>
											@endif
										</li>
										<li>
											@if($menuActive == 'about')
												<a class="nav-link active" href="{{ route('frontendAbout') }}">About</a>
											@else
												<a class="nav-link" href="{{ route('frontendAbout') }}">About</a>
											@endif
										</li>
										{{-- <li>
											<a class="nav-link" href="/login">Login</a>
										</li> --}}
									</ul>
								</nav>
							</div>
							<button class="btn header-btn-collapse-nav" data-toggle="collapse" data-target=".header-nav-main nav">
								<i class="fas fa-bars"></i>
							</button>
						</div>
						<div class="ml-2 order-1 order-lg-2">
							<ul class="header-social-icons social-icons d-none d-sm-block social-icons-clean ml-0">
								<li class="social-icons-facebook"><a href="http://www.facebook.com/" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
								<li class="social-icons-twitter"><a href="http://www.twitter.com/" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a></li>
								<li class="social-icons-linkedin"><a href="http://www.linkedin.com/" target="_blank" title="Linkedin"><i class="fab fa-linkedin-in"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>
