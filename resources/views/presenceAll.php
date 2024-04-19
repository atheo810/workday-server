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

		table {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 10px
		}

		td,
		th {
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
	</style>


</head>

<body>
	<div>


		<div style="max-width: 900px; margin-inline: auto;">
			<div style="text-align: center; margin-bottom: 10px;">
				<img src="data:image/png;base64, {{ base64_encode(file_get_contents(public_path('assets/images/gilang.jpeg'))) }}"
					style="display: inline-block; height: 100px; object-fit: contain;" alt="SDK Teologia Galang Kasih" />
			</div>

			<center><b class="uppercase" style="font-size: 18px;">PT Bengkel</b></center>

			<hr>
			<hr style="margin-top: 0.2%; margin-bottom: 0%;">

			<h2 style="margin-top: 8px;">
				<center><b class="uppercase">Rekap hasil Absensi {{ $name->fullname }}</b></center>
			</h2>

			<table id="table">
				<thead>
					<th class="uppercase">No</th>
					<th class="uppercase">Tgl/bln/thn</th>
					<th class="uppercase">Jam Masuk</th>
					<th class="uppercase">Jam Keluar</th>
				</thead>
				<tbody>
					@php
						$counter = 0;
					@endphp

					@foreach ($months as $month)
						@foreach ($month['presences'] as $day)
							@php
								$counter++;
							@endphp
							<tr>
								<td>{{ $counter }}</td>
								<td>{{ \Carbon\Carbon::parse($day->presence_date)->format('d-M-Y') }}</td>
								<td>{{ $day->clock_in }}</td>
								<td>{{ $day->clock_out }}</td>
							</tr>
						@endforeach
					@endforeach
				</tbody>
			</table>
		</div>
</body>

</html>
