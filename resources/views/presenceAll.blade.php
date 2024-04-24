<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>Laporan Absensi</title>

	<style type="text/css">
		body {
			font-family: Arial, sans-serif
		}

		.centering {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%)
		}

		.uppercase {
			text-transform: uppercase
		}

		table.utama {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 10px
		}

		td.utama,
		th.utama {
			border: 1px solid #000;
			padding: 8px;
			text-align: center
		}

		th {
			background-color: #f2f2f2
		}

		img {
			height: 70px
		}

		hr {
			border-color: #000;
			margin-bottom: .2%
		}

		h2 {
			margin-top: 0
		}

		.page_break {
			page-break-before: always;
		}
	</style>


</head>

<body>
	<div>


		<div style="max-width: 900px; margin-inline: auto;">
			<div style="text-align: center; margin-bottom: 10px;">
				<img
					src="data:image/png;base64, {{ base64_encode(file_get_contents(public_path('assets/images/logo-workday-tracker.png'))) }}"
					style="display: inline-block; height: 100px; object-fit: contain;" alt="Bengker eurocar" />
			</div>

			<center><b class="uppercase" style="font-size: 18px;">PT Bengkel</b></center>

			<hr>
			<hr style="margin-top: 0.2%; margin-bottom: 0%;">

			@php
				$lastEmployeeName = null;
			@endphp
			@foreach ($months as $month)
				<h3 style="margin-top: 8px;">
					<center><b class="uppercase">Rekap hasil Absensi {{ $month['employee_name'] . ' ' . $month['month'] }}</b>
					</center>
				</h3>



				<table id="table" class="utama">
					<thead>
						<th class="uppercase utama">No</th>
						<th class="uppercase utama">Tgl/bln/thn</th>
						<th class="uppercase utama">Jam Masuk</th>
						<th class="uppercase utama">Jam Keluar</th>
					</thead>
					<tbody>
						@php
							$counter = 0;
							\Carbon\Carbon::setLocale('id');
						@endphp

						@foreach ($month['presences'] as $day)
							@php
								$counter++;
							@endphp
							<tr class="utama">
								<td class="utama">{{ $counter }}</td>
								<td class="utama" style="text-align: left !important;">
									{{ \Carbon\Carbon::parse($day->presence_date)->translatedFormat('d-F-Y') }}</td>
								<td class="utama">{{ $day->clock_in ?? '--' }}</td>
								<td class="utama">{{ $day->clock_out ?? '--' }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<p style="margin-top: 10px; margin-bottom: 5px; font-size: 20px; font-weight: 400;">Note</p>
				<table style="margin-bottom: 20px;">
					<tr>
						<td>Jumlah absensi : </td>
						<td>
							@php
								$time_off_count = 0;
							@endphp
							@php
								$time_off_count += count($month['time_offs']);
							@endphp
							{{ $time_off_count }}
						</td>
						</td>
					</tr>
				</table>
				<div class="page_break"></div>
			@endforeach
		</div>
</body>

</html>
