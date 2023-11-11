@extends('Master.master')

@section('title','Add Student')

@section('content')
    <div class="pagetitle">
        <h1>Student Form</h1>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"></h5>

                <!-- General Form Elements -->
                <form action="{{route('adminStudentForm')}}" method="post">
                    @csrf
                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">NIS</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNis" value="{{old('inputNis')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Long Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLongName" value="{{old('inputLongName')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Nick Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNickName" value="{{old('inputNickName')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputEmail" value="{{old('inputEmail')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">DOB</label>
                        <div class="col-sm-10">
                            <input type="date" class="form-control" name="inputDate_of_Birth" value="{{old('inputDate_of_Birth')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Address</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" style="height: 100px" name="inputAddress">{{old('inputAddress')}}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Parent Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputParentName" value="{{old('inputParentName')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Bank Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputBankName" value="{{old('inputBankName')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label ">Sender Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputNamaPengirim" value="{{old('inputNamaPengirim')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label ">Account Number</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputRekening" value="{{old('inputRekening')}}">
                        </div>
                    </div>


                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">City</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputCity" value="{{old('inputCity')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Postal Code</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPostalCode" value="{{old('inputPostalCode')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">First Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone1" value="{{old('inputPhone1')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Second Phone</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputPhone2" value="{{old('inputPhone2')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Whatsapp</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputWhatsapp" value="{{old('inputWhatsapp')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Instagram</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputInstagram" value="{{old('inputInstagram')}}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label  class="col-sm-2 col-form-label">Line</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="inputLine" value="{{old('inputLine')}}">
                        </div>
                    </div>

                    <div class="justify-content-end d-flex mb-3">
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary me-3 p-2 ps-5 pe-5 h-25" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Term & Condition
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Term & Condition</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="accordion accordion-flush" id="accordionFlushExample">
                                            {{--indonesia--}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                                                        Bahasa Indonesia
                                                    </button>
                                                </h2>
                                                <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                                    <div class="accordion-body">
                                                        <div class="text-center">
                                                            <img src="{{asset('assets/img/logo-hitam.png')}}" alt="..." width="190px" height="190px">
                                                            <p>Rules and Regulations </p>
                                                        </div>
                                                        <br>
                                                        <b><u>Periode Sekolah</u></b> <br> <br>

                                                        Semester Pertama &emsp;: April – Juni <br>
                                                        Semester Kedua &emsp;: Juli – September <br>
                                                        Semester Ketiga &emsp;: Oktober – Desember <br>
                                                        Semester Empat &emsp;: Januari – Maret <br> <br>

                                                        Setiap semester terdiri dari 12 minggu. Libur semester mengikuti hari libur sekolah umum setempat. Belajar mengajar akan ditiadakan <br>
                                                        ketika hari libur nasional / tanggal merah di kalender, Konser, Graduation dan selama adanya Ujian Ballet, kelas tersebut tidak akan <br>
                                                        ada penggantian. <br> <br>

                                                        <b><u>Biaya Kursus</u></b> <br> <br>

                                                        Pembayaran dilakukan per semester dan harus segera dibayarkan paling lambat tanggal 10 diawal Semester. Setiap biaya <br>
                                                        keterlambatan akan diberlakukan biaya kursus normal yaitu 10% dari biaya kursus yang telah diinfokan. Siswa/i dapat ditangguhkan <br>
                                                        atau tidak dapat mengikuti kelas jika biaya kursus masih tidak dibayar hingga satu bulan setelah pertemuan pertama. <br>
                                                        Siswa baru diharuskan untuk menyelesaikan pembayaran setelah konfirmasi penerimaan bersamaan dengan biaya pendaftaran (tidak <br>
                                                        dapat dikembalikan). <br> <br>

                                                        Selain itu, siswa/i akan dikenakan biaya tambahan yang dibayarkan untuk Ujian Royal Academy of Dance (R.A.D) dan setiap kelas <br>
                                                        tambahan untuk menghadapi ujian tersebut. <br>
                                                        Setiap Biaya yang telah dibayarkan <b>tidak dapat dikembalikan</b> bila siswa/i absen / tidak mengikuti kelas. Dalam kasus sakit / cedera <br>
                                                        yang mengakibatkan siswa/i tidak diperbolehkan / tidak dapat mengikuti kelas, refund dapat diberikan sebesar (50%) dengan <br>
                                                        persyaratan adanya <b>tanda bukti / surat keterangan dari dokter yang bersangkutan. Tidak ada pengecualian dalam penggantian <br>
                                                            biaya kursus yang memilki kasus seperti : Liburan ke luar negri ataupun pertukaran pelajar. Melewatkan pertemuan <br>
                                                            didalam kelas tidak akan diganti.</b> <br> <br>

                                                        <b><u>PERATURAN SEKOLAH</u></b> <br> <br>

                                                        <b><u>Absensi</u></b> <br> <br>

                                                        Siswa/i harus selalu hadir dalam setiap pertemuan, jika didapati sering tidak hadir, maka dapat mempengaruhi perkembangan siswa/i <br>
                                                        itu sendiri dan bisa saja siswa/i tersebut tidak mendapatkan rekomendasi dalam mengikuti ujian RAD. <br>
                                                        Siswa/i yang tidak dapat hadir, harus memberikan informasi kepada Guru atau Staff Administrasi. <br> <br>

                                                        <b><u>Kehilangan Pertemuan / Pembatalan Kelas</u></b> <br> <br>
                                                        Jika siswa tidak bisa mengikuti kelas karena sakit, maka siswa bisa mengikuti kelas lain dengan tingkatan yang sama jika tersedia. <br>
                                                        Tapi sebelumnya harus meminta ijin terlebih dahulu kepada guru yang bersangkutan. Kelas tidak bisa diganti dengan alasan apapun <br>
                                                        kecuali sakit. Jika kelas dibatalkan oleh sekolah, maka siswa akan di tempatkan di kelas alternative, di pindahkan ke hari yang lain, <br>
                                                        atau siswa akan mendapatkan jam tambahan pada setiap pertemuannya. <br> <br>

                                                        <b><u>Penghentian Sekolah</u></b> <br> <br>

                                                        Siswa yang akan berhenti atau tidak akan melanjutkan sekolah lagi, maka siswa diharuskan untuk memberikan kabar secara tertulis <br>
                                                        kepada sekolah sekurang-kurangnya enam minggu sebelum semester berakhir. Dan sekolah hanya akan mengembalikan 50% dari sisa <br>
                                                        pembayaran di bulan yang belum terpakai. <br> <br>

                                                        <b><u>JADWAL KELAS </u></b> <br> <br>
                                                        Sekolah akan libur bila dalam satu bulan terdapat Minggu kelima ( tanggal 29,30 ,31 ). <br>
                                                        Sekolah berwenang untuk memindahkan ataupun menggabungkan kelas jika memang diperlukan. <br>
                                                        Total pertemuan belajar adalah 8x dalam 1 bulan, minimal pertemuan adalah 6x jika pertemuan terkena tanggal merah atau libur <br>
                                                        sekolah. Jika pertemuan kurang dari 6x maka siswa akan di tempatkan di kelas alternative, di pindahkan ke hari yang lain, atau siswa <br>
                                                        akan mendapatkan jam tambahan pada setiap pertemuannya.  <br> <br>

                                                        <b><u>PERATURAN DALAM KELAS</u></b> <br> <br>
                                                        • Siswa harus datang tepat waktu pada setiap pertemuan. <br>
                                                        • Siswa harus berpakaian rapi sesuai dengan yang sudah ditentukan pada setiap pertemuan <br>
                                                        • Siswa harus memberitahukan masalah medis ataupun cedera yang sedang dialami, untuk menghindari hal-hal yang tidak diinginkan.  <br>
                                                        • Siswa yang akan meninggalkan kelas sebelum kelas berakhir, harus memberitahukan kepada guru yang bersangkutan sebelum kelas dimulai. <br>
                                                        • Tidak boleh membawa makanan dan minuman kedalam kelas. <br>
                                                        • Orang tua tidak boleh berada di dalam kelas kecuali mendapatkan ijin dari guru yang bersangkutan. <br>
                                                        • Orang tua tidak diperbolehkan mengambil gambar maupun video selama kelas berlangsung. <br> <br>

                                                        Sekolah tidak akan bertanggung jawab atas cedera yang terjadi karena kondisi medis yang tidak kami ketahui, Namun kami akan <br>
                                                        berusaha semampu kami untuk menjaga siswa selama kelas berlangsung. <br> <br>

                                                        <b><u>UJIAN</u></b> <br> <br>
                                                        Ujian Royal Academy of Dance (R.A.D) di adakan satu kali dalam setahun. Setiap siswa/i tidak secara otomatis terdaftar sebagai<br>
                                                        peserta ujian. Siswa/i yang sudah memenuhi kriteria akan di pilih oleh guru yang bersangkutan dan <b>keputusan guru yang <br>
                                                            bersangkutan merupakan keputusan final yang tidak dapat diganggu gugat.</b> <br> <br>

                                                        Bagi siswa/i yang telah di pilih untuk mengikuti ujian wajib hadir pada setiap pertemuan. Sekolah berhak untuk membatalkan siswa/i <br>
                                                        untuk mengikuti ujian, jika siswa/i tidak di siplin dalam kehadiran atau tidak menunjukan perkembangan seperti yang diharapkan, <br>
                                                        tanpa pengembalian biaya ujian. <br> <br>

                                                        <b>Siswa/i tidak dapat loncat kelas tanpa mengikuti / menyelesaikan ujian pada level / grade yang ditentukan.</b> <br>
                                                        Siswa/i <b>tidak dapat melanjutkan / mengikuti</b> level ( grade ) selanjutnya, jika <b>tidak lolos / lulus dalam ujian sebelumnya.</b> Maka <br>
                                                        siswa/i tersebut diwajibkan untuk kembali mengulang level / grade yang sama. <br> <br>

                                                        <b><u>MURID BARU</u></b> <br> <br>

                                                        Sekolah akan memberikan 2x pertemuan awal untuk menentukan / menempatkan level ( grade ) yang sesuai dengan kemampuan <br>
                                                        siswa/i. <b>keputusan guru yang bersangkutan merupakan keputusan final yang tidak dapat diganggu gugat.</b> <br>

                                                        Bagi siswa/i Royal Academy of Dance (RAD) yang ingin melanjutkan level / grade <b>harus menunjukkan sertifikat terakhir.</b> Jika <br>
                                                        tidak dapat menunjukkan sertifikat terakhir maka siswa/i <b>diwajibkan mengikuti 2x pertemuan pada kelas yang telah ditentukan,</b> <br>
                                                        untuk menentukan / menempatkan level ( grade ) yang sesuai dengan kemampuan siswa/i. </b>keputusan guru yang bersangkutan <br>
                                                        merupakan keputusan final yang tidak dapat diganggu gugat.</b> <br> <br>

                                                        <b><u>KONSER SEKOLAH</u></b> <br> <br>
                                                        Konser sekolah akan diadakan setiap dua tahun sekali bertepatan dengan pengumuman nilai ujian siswa. Siswa/i <b>diwajibkan <br>
                                                            mengikuti konser sekolah dan membeli kostum (jika diperlukan)</b> ataupun Tiket Konser untuk penonton ( orang tua, dll ). <br>
                                                        Pertemuan kelas yang terkena jadwal konser tidak akan ada penggantian. <br> <br>

                                                        <b><u>PERTUNJUKAN SENI SEKOLAH DAN SEMINAR BELAJAR</u></b> <br> <br>
                                                        Pertunjukan seni sekolah atau seminar belajar akan diadakan beberapa kali dalam setahun (bertepatan dengan pengumuman nilai ujian <br>
                                                        siswa). Siswa/i <b>diwajibkan mengikuti pertunjukan seni sekolah dan membeli kostum (jika diperlukan)</b> ataupun <b>biaya seminar <br>
                                                            belajar (jika mengikuti).</b> Pertemuan kelas yang terkena jadwal pertunujukan seni sekolah tidak akan ada penggantian. <br> <br>

                                                        <b><u>PARENTS’ WEEK</u></b> <br> <br>

                                                        <u>Orang tua boleh masuk ke dalam kelas pada minggu ketiga di bulan Desember untuk melihat perkembangan dan kemajuan siswa.</u> <br> <br>

                                                        <b><u>BARANG BERHARGA</u></b>  <br> <br>

                                                        Siswa/i sangat tidak disarankan membawa uang yang berlebihan / benda benda berharga lainnya ketika menghadiri kelas. Sekolah <br>
                                                        tidak dapat dan tidak akan bertanggung jawab atas uang tunai atau barang bernilai lainnya jika terjadi kehilangan. <br> <br>

                                                        <u><b>PERIHAL LAINNYA</u></b> <br> <br>
                                                        Siswa dan orang tua sangat disarankan untuk <b>memeriksa secara teratur untuk pengumuman penting dan informasi lain yang <br>
                                                            diposting di papan pengumuman, pesan digital ataupun media sosial.</b> <br> <br>

                                                        Orang tua harus <b>memberitahu kepada Sekolah untuk setiap perubahan alamat rumah dan kontak,</b> kegagalan sekolah untuk <br>
                                                        dapat menghubungi Orang Tua atau Siswa/i tidak akan menjadi tanggung jawab atas ketidaknyamanan / masalah yang mungkin <br>
                                                        timbul di masa yang akan datang. <br> <br>

                                                        <input class="form-check-input" type="checkbox" value="" id="tnci">
                                                        <label class="form-check-label" for="tnci">
                                                            Saya Setuju
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{--english--}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
                                                        Bahasa Inggris
                                                    </button>
                                                </h2>
                                                <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                                    <div class="accordion-body">
                                                        <div class="text-center">
                                                            <img src="{{asset('assets/img/logo-hitam.png')}}" alt="..." width="190px" height="190px">
                                                            <p>Rules and Regulations </p>
                                                        </div>
                                                        <br>
                                                        <b><u>SCHOOL TERM</u></b> <br> <br>

                                                        1st term &emsp;: April – June <br>
                                                        2nd term &emsp;: July – September <br>
                                                        3rd term &emsp;: October – December <br>
                                                        4th term &emsp;: January – March <br> <br>

                                                        Each term comprises twelve weeks. Term breaks are scheduled during local school holidays. Classes will not be conducted on <br>
                                                        public holidays, ballet concert, graduation and during ballet examinations. Such classes will not be replaced. <br> <br>

                                                        <b><u>FEES</u></b> <br> <br>

                                                        Fee payments are on <b><u>per term basis</u></b> and should be promptly paid within 10th of the new term. <b><u>A late payment fee</u></b> will have <br>
                                                        to pay normal fee, by 10 % from the inform fee course will be charged after the due dates, respectively. Students may be <br>
                                                        suspended or terminated if fees are not paid at the end of the term. <br>
                                                        New students are required to settle payment upon confirmation of acceptance together with registration fees (non-refundable). <br> <br>

                                                        In addition, students have to bear the fees for the Royal Academy of Dance (R.A.D) examinations and any extra coaching <br>
                                                        classes prior to examinations. <br> <br>

                                                        Fees are <b>not refundable</b> on account of student’s absence from classes. In case of illness/ injury, a partial refund(50%) may <b>be <br>
                                                            considered</b> with proof of a medical certificate. <b>No concession of fees will be granted for period overseas vacations and <br>
                                                            school exchange program. Missed classes will not be replaced.</b> <br> <br>

                                                        <b><u>SCHOOL REGULATIONS</u></b> <br> <br>

                                                        <b><u>Attendance</u></b> <br> <br>

                                                        Students must keep to regular attendance. If not, their progress in class may be affected and may <br>
                                                        not be considered for the RAD exams. The teacher or Administrator should be kept informed if students are unable to attend <br>
                                                        any class. <br> <br>

                                                        <b><u>Missed/cancelled classes</u></b> <br> <br>

                                                        Should students miss a classdue to illness, they may attend another class of similar level,<b>if available.</b> However, permission <br>
                                                        should be obtained from the teacher prior to any replacement class. Classes are not replaceable for absence due to other reasons <br>
                                                        except illness. <br> <br>

                                                        Should a class be cancelled by the school, students will be referred to an alternative class, or re-scheduled day, or given <br>
                                                        additional time during existing class until the lost time has been compensated. <br> <br>

                                                        <b><u>TERMINATION</u></b> <br> <br>

                                                        Students wishing to discontinue classes are required to give written notice to the School half a term (6 weeks) in advance, <br>
                                                        <b>failing which half a term fee must be paid in lieu of such notice.</b> <br> <br>

                                                        <b><u>CLASS TIME-TABLE</u></b> <br> <br>

                                                        Classes will not be conducted when in one month there is the fifth week (29,30,31) <br>
                                                        School Class time-table for each term will be determined by the School which reserves the right to combine, transfer or <br>
                                                        dissolve a class anytime as necessary. <br>
                                                        There are 8x meetings in total a month, minimum 6x meetings if there are school or public holidays. If it less than 6x meetings, <br>
                                                        the student will be referred to an alternative class, or re-scheduled day, or given additional time during existing class until the <br>
                                                        lost time has been compensated. <br> <br>

                                                        <b><u>CLASSROOM REGULATIONS</u></b> <br> <br>

                                                        • Students must be on time for every class. <br>
                                                        • Students must be suitably and neatly attired for class.  <br>
                                                        • The school should be informed of medical problems or physical injuries in order that the student is not over-strained during class. <br>
                                                        • Students who need to leave class before lesson ends, should notify the teacher before class commences. <br>
                                                        • No food or drinks are allowed in the studio. <br>
                                                        • Parents are not allowed in the class, except with teacher permission. <br>
                                                        • Parents are not allowed to take any picture or video during class. <br>
                                                        The school will not be held responsible for any injury sustained due to an unknown medical condition, accidental reasons or <br>
                                                        improper execution of instructions on the part of the student. However, every care will be taken to ensure the well being of students during class.<br> <br>

                                                        <b><u>EXAMINATIONS</u></b> <br> <br>

                                                        Royal Academy of Dance (R.A.D) examinations are held twice a year. Student are <b>not</b> automatically entered for examinations. <br>
                                                        Those who have shown consistenly good standard will be selected by the Teacher / Principal. The teacher’s / Principal’s <br>
                                                        decision is final. <br> <br>

                                                        It is compulsory for students who have been selected for examinations to attend two classes per week (for children grades) or <br>
                                                        three classes per week ( for Vocational grades). The school reserves the right to withdraw any student from taking <br>
                                                        examinations if she/he fails to keep to regular attendance or not progressing as expected, <b>without refund of examination fees.</b> <br> <br>

                                                        <b>The student cannot skip the level without passing the EXAM. <br>
                                                            The student cannot continue to the next level / grade,</b> If the student <b>does not pass or fails</b> the Exam and the student <b>has to <br>
                                                            continue with the same level or grade.</b> <br> <br>

                                                        <b><u>NEW STUDENT</u></b> <br> <br>

                                                        The School will give free trial 2x to give the right level or grade for the new student. The teacher’s / Principal’s decision is final. <br>
                                                        The New Student from RAD has to show the last certicate for continuing class. The student has to take the free trial 2x, If the <br>
                                                        student cannot show the certificate. <br> <br>

                                                        <b><u>SCHOOL CONCERT</u></b> <br> <br>

                                                        School concert will be organized once every two years with children graded examinations (subject to change). Students <b>have <br>
                                                            to join the Concert</b> and bear the fees for the costumes or the ticket for the viewer seat. Meeting class will not be replaced. <br> <br>

                                                        <b><u>SCHOOL PERFORM & Workshop</u></b> <br> <br>

                                                        A few School performances or Workshops will be organized in a year with children graded examinations (subject to change).<br>
                                                        Students have to bear the fees for the costumes or workshop fees or the tickets. Meeting class will not be replaced. <br> <br>

                                                        <b><u>PARENTS’ WEEK</u></b> <br> <br>

                                                        Classes will be open to parents to observe on the last week of the 3rd term (December).<br> <br>

                                                        <b><u>VALUABLES</u></b> <br> <br>

                                                        Students are strongly advised not to bring large amount of cash or valuables when attending classes. The school cannot and <br>
                                                        will not be held responsible for any cash or items of value which are lost or stolen at its premises. <br> <br>

                                                        <b><u>OTHER MATTERS</u></b> <br> <br>

                                                        Students and parents are strongly advised to <b>check regularly for important announcements and other information posted <br>
                                                            on the notice board, digital message and social media.</b> <br> <br>

                                                        Parents should <b>notify the School of any change in home address and contacts,</b> failing which the school will not be held <br>
                                                        responsible for any inconvenience/issues that may arise. <br> <br>

                                                        <input class="form-check-input" type="checkbox" value="" id="tnce">
                                                        <label class="form-check-label" for="tnce">
                                                            I Agree
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>




                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-success p-2 ps-5 pe-5" id="submit">
                            Submit
                        </button>
                    </div>

                    @if($errors->any())
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{$error}}
                            </div>
                        @endforeach
                    @endif

                </form><!-- End General Form Elements -->

            </div>
        </div>
    </section>

    <script>
        $(document).ready(function(){
            const myModal = document.getElementById('exampleModal');
            $("#submit").hide();

            $("#tnce").change(function(){
                if($(this).prop('checked') == false){
                    $("#submit").hide();
                } else {
                    $("#submit").show();
                }
            })

            $("#tnci").change(function(){
                if($(this).prop('checked') == false){
                    $("#submit").hide();
                } else {
                    $("#submit").show();
                }
            })

            myModal.addEventListener('shown.bs.modal', () => {
                // myInput.focus()
            })
        });

    </script>

@endsection
