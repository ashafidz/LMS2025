DAFTAR ISI
Table of Contents
DAFTAR ISI 1
DAFTAR GAMBAR 2
DAFTAR TABEL 3
BAB I PENDAHULUAN 3
1.1 Latar Belakang Masalah 3
1.2 Rumusan Masalah 4
1.3 Tujuan 5
BAB II TINJAUAN PUSTAKA 5
2.1 Penelitian yang Relevan 5
2.2 Landasan Teori 10
2.2.1 E-Learning 10
2.2.2 Ujian Online dan Integritas Akademik 10
2.2.3 Website 10
2.2.4 HTML 10
2.2.5 CSS 11
2.2.6 Javascript 11
2.2.7 Bootstrap 11
2.2.8 PHP 11
2.2.9 Laravel 12
2.2.10 MySQL 12
2.2.11 TensorFlow.js & MediaPipe Face Mesh 12
2.2.12 UML (Unified Modeling Language) 12
2.2.13 Black Box Testing 13
2.2.14 Algoritma Fisher-Yates Shuffle 13
2.2.15 Use Case Diagram 13
2.2.16 Activity Diagram 14
2.2.17 Entity Relationship Diagram 15
2.2.18 www 16
BAB III ANALISA DAN PERANCANGAN 16
3.1 Analisis Permasalahan 16
3.2 Analisis Pemecan Masalah 16
3.2.1 Metode Pengambilan Data (user requirement) 18
3.2.2 Metode Pengembangan Sistem 18
3.2.3 Analisis Kebutuhan Sistem (fungsional dan non fungsional sistem) 18
3.3 Perancangan Sistem 18
3.3.1 Use Case Diagram 18
3.3.2 Activity Diagram 18
3.3.3 Relasi antar Tabel 18
3.3.4 Arsitektur Diagram 18
3.3.5 Antarmuka Pengguna (User Interface) 18
3.4 Pengujian Sistem 18
BAB IV JADWAL KEGIATAN 18
4.1 Jadwal 18
4.2 Pembagian Tugas 18
DAFTAR PUSTAKA 18

DAFTAR GAMBAR
Gambar 2.1 Simbol-Simbol Use Case Diagram 12
Gambar 2.2 Simbol-Simbol Activity Diagram 13

DAFTAR TABEL
Tabel 2.1 Penelitian yang relevan. 6

BAB I PENDAHULUAN
1.1 Latar Belakang Masalah
Perkembangan teknologi informasi dan komunikasi telah membawa transformasi fundamental pada berbagai sektor, terutama pada sektor pendidikan. Munculnya era digital telah mendorong adopsi e-learning dan course marketplace secara masif, yang menawarkan metode pembelajaran yang lebih fleksibel dan dapat diakses tanpa batasan ruang maupun waktu. Platform ini bergantung pada sebuah Learning Management System (LMS) untuk mengelola materi, pengguna, dan yang paling penting, sistem asesmen atau evaluasi pembelajaran. Asesmen online, seperti kuis dan ujian, menjadi komponen kritis untuk mengukur pemahaman dan pencapaian peserta didik secara objektif.
Penelitian ini dilakukan pada E-Learning EduGames, yang dikelola oleh Wahana Media Digital. EduGames menyediakan berbagai kursus online di bidang teknologi dan industri kreatif yang dapat diakses oleh masyarakat umum. Sebagai platform yang sedang berkembang, EduGames menjadi lokasi penelitian yang ideal untuk implementasi dan pengembangan fitur baru. Kredibilitas sertifikat dan kualitas lulusan dari platform ini sangat bergantung pada validitas sistem asesmen yang digunakan.
Proses bisnis yang menjadi fokus utama penelitian ini adalah pelaksanaan asesmen online (kuis) di platform EduGames. Saat ini, proses bisnis berjalan dengan alur standar: Instructor membuat soal kuis melalui sistem, Student mengerjakan kuis tersebut dalam batas waktu yang ditentukan, dan sistem akan melakukan penilaian secara otomatis untuk menghasilkan skor akhir. Proses ini, meskipun efisien, belum memiliki mekanisme pengawasan yang memadai untuk menjamin integritas pelaksanaan kuis.
Kendala utama dari proses bisnis saat ini adalah rendahnya integritas asesmen. Sistem yang ada sangat rentan terhadap praktik kecurangan akademik. Student dapat dengan mudah melakukan deteksi perpindahan tab (tab switching) untuk mencari jawaban di internet atau membuka aplikasi lain, serta melakukan kecurangan secara fisik seperti menoleh atau melihat ke arah lain untuk menyontek tanpa terdeteksi oleh sistem. Hal ini menciptakan sebuah gap (kesenjangan) antara tujuan ideal asesmen yang mengukur pemahaman murni Student dan realitas pelaksanaan yang malah menjadi seperti mengukur kemampuan menyontek Student. Oleh karena itu, diperlukan sebuah inovasi berupa implementasi sistem keamanan yang dapat memitigasi risiko-risiko tersebut secara teknis.
Platform EduGames dibangun menggunakan tumpukan teknologi web modern, yaitu framework Laravel dengan basis data MySQL. Kajian literatur menunjukkan bahwa platform web adalah arsitektur yang umum digunakan untuk membangun LMS. Penelitian relevan oleh (Anam et al., 2022) juga mengembangkan Learning Management System (LMS) sebagai aplikasi berbasis web untuk kursus online. Penggunaan framework yang matang seperti Laravel memungkinkan platform untuk dikembangkan lebih lanjut dan diintegrasikan dengan teknologi frontend yang canggih, seperti JavaScript API, untuk menangani fungsionalitas yang lebih kompleks.
Untuk menjawab gap pada fitur kuis platfom E-Learning EduGames ini, penelitian ini merujuk pada kajian ilmiah yang telah berhasil mengimplementasikan fitur serupa. Penelitian oleh (Bimantoro et al., 2024) yang berjudul "Learning Management System (LMS) Pada Kursus Online Berbasis Deteksi Kecurangan Ujian Menggunakan Model Mediapipe Face Mesh" secara spesifik berhasil mengimplementasikan dua fitur inti. Fitur pertama adalah "deteksi kecurangan penyalahgunaan aplikasi seperti membuka tab baru pada browser dan berpindah-pindah aplikasi". Fitur kedua adalah deteksi wajah menggunakan "model Facemesh MediaPipe" untuk mengidentifikasi perilaku mencurigakan seperti "melihat ke arah samping, tengok kanan dan kiri serta menengok ke bawah dan ke atas". Referensi ini memvalidasi secara teknis dan akademis bahwa kedua fitur keamanan tersebut dapat diimplementasikan secara bersamaan di lingkungan LMS berbasis web.
Berdasarkan permasalahan rendahnya integritas kuis di platform EduGames dan didukung oleh validasi teknis dari penelitian sebelumnya, maka solusi yang diusulkan adalah Pengembangan Sistem Keamanan Kuis Online. Sistem ini akan berfokus pada implementasi fitur-fitur baru yaitu Deteksi Perpindahan Tab, Pengacakan Penyajian Soal, dan Deteksi Kamera. Oleh karena itu, penelitian ini mengambil judul: "Implementasi Fitur Keamanan Ujian Online Menggunakan Algoritma Fisher-Yates Shuffle dan MediaPipe Face Mesh pada Platform E-Learning EduGames"
1.2 Rumusan Masalah
Berdasarkan latar belakang masalah yang telah diuraikan, maka dirumuskan pertanyaan penelitian sebagai berikut:

1.  Apakah implementasi Algoritma Fisher-Yates Shuffle dapat menghasilkan urutan soal yang acak dan berbeda untuk setiap peserta ujian sehingga meminimalisir kecurangan?
2.  Apakah fitur Deteksi Kamera menggunakan MediaPipe Face Mesh mampu mengidentifikasi perilaku kecurangan berupa pergerakan wajah menoleh ke samping (kanan/kiri), menunduk, dan mendongak dalam durasi tertentu dengan tingkat akurasi yang memadai?
3.  Apakah sistem dapat mendeteksi dan merekam aktivitas Perpindahan Tab secara akurat sebagai data pelanggaran selama sesi ujian?
4.  Apakah Laporan Integritas yang dihasilkan sistem sesuai dengan data pelanggaran yang terekam dan dapat digunakan sebagai dasar evaluasi oleh Instruktur?
    1.3 Tujuan
    Sejalan dengan rumusan masalah di atas, tujuan dari penelitian ini adalah:
5.  Mengimplementasikan Algoritma Fisher-Yates Shuffle dan menguji kemampuannya dalam menghasilkan distribusi soal yang acak dan unik untuk setiap peserta ujian.
6.  Mengimplementasikan fitur Deteksi Kamera berbasis MediaPipe Face Mesh dan mengukur tingkat akurasinya dalam mengenali perilaku kecurangan berupa pergerakan wajah menoleh ke samping, menunduk, dan mendongak.
7.  Mengimplementasikan sistem deteksi Perpindahan Tab dan memverifikasi kemampuannya dalam mencatat aktivitas pelanggaran.
8.  Menghasilkan Laporan Integritas bertingkat (per Siswa, per Kuis, per Kursus) dan memvalidasi kesesuaiannya dengan data pelanggaran yang terekam.
    BAB II TINJAUAN PUSTAKA
    2.1 Penelitian yang Relevan
    Dalam pembangunan sistem ini diperlukan beberapa penelitian yang berkaitan dengan keamanan ujian online, deteksi kecurangan, dan Learning Management Sistem (LMS) sebagai referensi atau bahan acuan dalam perancangan sistem yang dibangun. Terdapat lima contoh penelitian terdahulu terkait, antara lain sebagai berikut.
    Penelitian yang pertama, penelitian yang dilakukan oleh Bimantoro, dkk. dengan judul “Learning Management System (LMS) Pada Kursus Online Berbasis Deteksi Kecurangan Ujian Menggunakan Model Mediapipe Face Mesh” pada tahun 2024. Tujuan penelitian tersebut adalah untuk mengembangkan LMS yang dapat mendeteksi kecurangan ujian secara otomatis menggunakan teknologi computer vision. Penelitian ini menghasilkan sistem yang mampu mendeteksi perilaku mencurigakan seperti menoleh dan membuka tab baru (tab switching) menggunakan model MediaPipe Face Mesh dan TensorFlow.js (Bimantoro et al., 2024).
    Penelitian yang kedua, penelitian yang dilakukan oleh Anam, dkk. Dengan judul “Design and Build a Web-based Learning Management System Using the Laravel Framework” pada tahun 2022. Tujuan penelitian tersebut adalah untuk merancang dan membangun LMS berbasis web yang efisien menggunakan kerangka kerja PHP modern. Penelitian ini menghasilkan sistem LMS yang terstruktur dengan baik menggunakan arsitektur MVC (Model-View-Controller) dari Laravel, yang memvalidasi penggunaan teknologi tersebut untuk platform edukasi (Anam & Sifaunajah, 2022).
    Penelitian yang ketiga, penelitian yang dilakukan oleh Lubis, Antoni, dan Aulia dengan judul “Implementasi Algoritma Fisher-Yates Shuffle Pada Sistem Ujian Online Berbasis Website di SMP Swasta Ir. H. Djuanda Tebing Tinggi” pada tahun 2025. Tujuan penelitian ini adalah untuk mengimplementasikan algoritma Fisher-Yates Shuffle guna mengacak urutan soal dan jawaban secara merata, sehingga setiap peserta mendapatkan komposisi soal yang berbeda untuk meminimalisir kecurangan. Penelitian ini menghasilkan sistem ujian online berbasis website yang terbukti efektif dalam mendistribusikan soal secara acak dan mengurangi peluang kolusi antar siswa selama ujian berlangsung (Lubis et al., 2025).
    Penelitian yang keempat, penelitian yang dilakukan oleh Agustinus dan Engel dengan judul “Real Time Online Exam Proctoring System in Higher Education Using WebRTC Technology” pada tahun 2023. Tujuan penelitian tersebut adalah untuk mengembangkan sistem pengawasan ujian real-time guna mengatasi masalah kecurangan seperti penggunaan dual monitor atau membuka aplikasi lain. Penelitian ini menghasilkan sistem proctoring berbasis web dengan fitur unggulan Active Tab & Window Detection, yang mampu mendeteksi jika peserta berpindah tab atau jendela aplikasi lain dan secara otomatis mengunci halaman ujian peserta tersebut (Agustinus & Engel, 2024).
    Penelitian yang kelima, penelitian yang dilakukan oleh Melani dengan judul “Rancang Bangun Aplikasi Web untuk Platform E-Learning dengan Fitur Pembelajaran Interaktif dan Ujian Online” pada tahun 2023. Tujuan penelitian tersebut adalah untuk mengembangkan platform pembelajaran daring yang fleksibel, dilengkapi dengan modul interaktif dan fitur evaluasi otomatis. Penelitian ini menghasilkan aplikasi berbasis web yang memungkinkan pengguna mengakses materi pembelajaran berupa video dan kuis, serta melaksanakan ujian online dengan hasil penilaian yang instan untuk memantau kemajuan belajar (Melani, 2023).
    Ringkasan mengenai penelitian terdahulu ditunjukkan pada Tabel 2.1 berikut.
    Tabel 2.1 Penelitian yang relevan.
    No Judul Tujuan Fitur Hasil
9.  Learning Management System (LMS) Pada Kursus Online Berbasis Deteksi Kecurangan Ujian Menggunakan Model Mediapipe Face Mesh Penelitian ini bertujuan meningkatkan integritas pembelajaran online melalui pengembangan sistem manajemen pembelajaran (LMS) inovatif. 1. Deteksi Wajah & Orientasi Kepala (Face Mesh).
    2.Deteksi Perpindahan Tab & Aplikasi (Tab Switching).
10. Penutupan Tes Otomatis.
    Pada peningkatan kualitas dan integritas pembelajaran online melalui implementasi LMS yang efektif dan dapat
    diandalkan.
11.     Design and Build a Web-based Learning Management Sistem Using the Laravel Framework.	Penelitian ini bertujuan untuk menghasilkan media pembelajaran berupa modul materi untuk semua orang yang mempunyai minat di bidang pemrogaman juga untuk mereka yang mengalami keterbatasan waktu untuk mengikuti pembelajaran pada perkuliahan atau akademika
    lainya. 1.Manajemen Pengguna.
    2.Manajemen Materi Kursus.
    3.Akses Pembelajaran Web.
    Menghasilkan sebuah sistem LMS yang diharapkan dapat memudahkan guru dalam mendistribusikan materi dan memudahkan siswa dalam mengakses materi pembelajaran. Sehingga pengguna dapat dengan mudah dalam berinteraksi dan lebih memberikan hasil yang efisien di dalam proses pembelajaran.
12. Implementasi Algoritma Fisher-Yates Shuffle Pada Sistem Ujian Online Berbasis Website di SMP Swasta Ir. H. Djuanda Tebing Tinggi Mengimplementasikan algoritma Fisher-Yates Shuffle untuk mengacak urutan soal dan jawaban guna mengurangi potensi kecurangan kolusi antar siswa
    1. Manajemen Bank Soal.
13. Pengacakan Soal (Randomization).
14. Ujian Online Real-time.

    Menghasilkan sistem ujian yang efektif mengacak urutan soal secara merata dan adil bagi setiap peserta, sehingga meminimalisir kecurangan akibat kesamaan urutan soal.

15. Real Time Online Exam Proctoring System in Higher Education Using WebRTC Technology Membangun sistem pengawasan ujian real-time untuk mencegah kecurangan seperti penggunaan dual monitor atau membuka aplikasi lain 1. Active Tab & Window Detection.
16. Live Proctoring.
17. Exam Lock (Kunci Ujian Otomatis).
    Sistem terbukti efektif mendeteksi perpindahan tab/jendela aktif dan memberikan peringatan otomatis (alert) serta mengunci layar ujian jika terjadi pelanggaran
18. Rancang Bangun Aplikasi Web untuk Platform E-Learning dengan Fitur Pembelajaran Interaktif dan Ujian Online Mengembangkan aplikasi web E-Learning yang menyediakan pengalaman belajar fleksibel dengan fitur evaluasi otomatis.
    1. Modul Pembelajaran Interaktif.
19. Video Pembelajaran.
20. Ujian Online dengan Hasil Instan.
    Menghasilkan platform yang memfasilitasi akses materi fleksibel dan evaluasi mandiri melalui ujian online yang memberikan umpan balik nilai secara langsung

Berdasarkan pada penelitian relevan yang ditunjukkan pada Tabel 2.1, maka diketahui bahwa “Implementasi Fitur Keamanan Ujian Online Menggunakan Algoritma Fisher-Yates Shuffle dan MediaPipe Face Mesh pada Platform E-Learning EduGames” memiliki perbedaan dan kebaruan (novelty) dibandingkan penelitian terdahulu, antara lain:

1. Mengintegrasikan tiga fitur keamanan sekaligus (Deteksi Perpindahan Tab, Pengacakan Penyajian Soal, dan Deteksi Kamera) ke dalam satu platform learning management system yang utuh, berbeda dengan penelitian sebelumnya yang cenderung pada satu atau dua aspek keamanan saja.
2. Menerapkan mekanisme “Laporan Integritas” bertingkat (per Siswa, per Kuis, per Kursus) sebagai fitur pendukung keputusan bagi Instruktur untuk melakukan revisi skor secara bijak, melampaui sekadar sistem hukuman otomatis yang kaku.
   2.2 Landasan Teori
   Dalam membangun sistem keamanan kuis pada E-Learning ini, diperlukan adanya landasan teori sebagai bahan acuan dalam menyelesaikan penelitian ini. Adapun landasan teorinya sebagai berikut.
   2.2.1 E-Learning
   E-Learning didefinisikan sebagai sarana pendidikan yang menggabungkan motivasi diri, komunikasi, efisiensi, dan teknologi. E-Learning menghilangkan jarak dan perjalanan fisik karena konten pembelajaran dirancang menggunakan media yang dapat diakses dari terminal komputer yang dilengkapi dengan baik atau teknologi lain yang memiliki akses internet (Berman, 2006).
   2.2.2 Ujian Online dan Integritas Akademik
   Ujian online merupakan metode evaluasi yang dilakukan melalui jaringan internet, memungkinkan peserta untuk mengerjakan tes tanpa batasan fisik ruang kelas (Siregar et al., 2021). Salah satu tantangan utama dalam ujian online adalah menjaga integritas akademik. Kecurangan akademik didefinisikan sebagai tindakan tidak jujur yang melanggar aturan evaluasi. Bentuk kecurangan digital meliputi tab switching (berpindah ke tab atau aplikasi lain untuk mencari jawaban) dan bantuan eksternal fisik.
   2.2.3 Website
   Website merupakan koleksi halaman informasi digital yang mencakup elemen multimedia seperti teks, visual, dan audio, yang didistribusikan melalui koneksi internet untuk akses publik. Struktur dasar halaman ini dibangun menggunakan kode standar HTML. Kode tersebut berfungsi sebagai instruksi bagi web browser untuk merender atau menerjemahkan skrip menjadi tampilan visual yang dapat dibaca dan dipahami oleh manusia (Abdullloh, 2018).
   2.2.4 HTML
   HTML adalah bahasa markah yang berfungsi sebagai kerangka dasar dalam pembuatan dokumen web. Bahasa ini memungkinkan pengembang untuk mendefinisikan struktur konten, seperti tata letak teks dan elemen visual lainnya, tanpa memerlukan keahlian pemrograman yang rumit. Meskipun terdapat beberapa versi seperti HTML 4.01 dan XHTML, HTML5 kini menjadi standar baru yang menawarkan fungsionalitas lebih luas dan dukungan peramban yang lebih baik (Robbins, 2012).
   2.2.5 CSS
   CSS (Cascading Style Sheets) adalah mekanisme yang digunakan untuk mendeskripsikan tampilan visual dari sebuah dokumen web, terpisah dari kontennya. CSS memungkinkan pengembang untuk mengontrol tipografi, warna, dan tata letak secara terpusat, sehingga memudahkan pengelolaan desain situs secara keseluruhan. Selain itu, CSS memastikan konten dapat diakses dengan baik melalui berbagai perangkat dan media, termasuk pembaca layar (screen readers) dan perangkat mobile (Robbins, 2012).
   2.2.6 Javascript
   JavaScript adalah bahasa pemrograman skrip yang dirancang untuk memberikan fungsionalitas interaktif pada situs web. Berbeda dengan HTML atau CSS yang statis, JavaScript bekerja dengan memanipulasi elemen halaman dan gaya melalui struktur DOM (Document Object Model). Implementasinya mencakup berbagai fitur penting seperti validasi formulir, manajemen sesi pengguna, dan pembuatan antarmuka yang responsif. Meskipun menuntut kurva belajar pemrograman, ketersediaan skrip siap pakai pada berbagai perangkat lunak web-authoring memudahkan integrasinya dalam pengembangan web (Robbins, 2012).
   2.2.7 Bootstrap
   Bootstrap adalah salah satu toolkit antarmuka pengguna (UI) frontend paling populer di dunia dan dilengkapi dengan solusi yang mudah digunakan dansiap pakai untuk membangun situs web responsif menggunakan komponen, utilitas, plugin JavaScript dan lainnya. Anda dapat menyesuaikan Bootstrap 5 dengan Sass untuk menciptakan tata letak yang unik dan menonjol dari yang lain. Belajar cara menyesuaikan Bootstrap 5 memungkinkan seorang pengembang untuk menciptakan sesuatu yang unik yang tidak terlihat seperti Bootstrap (Jensen, 2022).
   2.2.8 PHP
   PHP (Hypertext Preprocessor) adalah bahasa pemrograman open-source yang berfungsi memproses konten halaman web atau dokumen lainnya sebelum output-nya dikirim ke browser. PHP sangat populer dalam pengembangan web karena fleksibilitasnya dalam menangani formulir dan integrasinya yang kuat dengan berbagai perangkat lunak basis data. Hal ini memungkinkan pembuatan halaman web yang dinamis dan pengelolaan data yang efisien, menjadikannya elemen krusial dalam ekosistem aplikasi web modern (Technology, 2015).
   2.2.9 Laravel
   Laravel didefinisikan sebagai sebuah kerangka kerja (framework) aplikasi web dengan sintaksis yang ekspresif dan elegan. Sebagai sebuah framework, Laravel menyediakan struktur dasar dan titik awal dalam pengembangan aplikasi, sehingga memungkinkan pengembang untuk berfokus pada penciptaan fungsionalitas utama tanpa terbebani oleh detail teknis yang rumit. Laravel dirancang untuk memberikan pengalaman pengembangan yang optimal dengan menyediakan fitur-fitur handal seperti dependency injection, lapisan abstraksi basis data yang ekspresif, manajemen antrian (queues), penjadwalan tugas (scheduled jobs), serta pengujian unit dan integrasi. Fleksibilitas ini menjadikan Laravel relevan baik bagi pemula maupun pengembang berpengalaman (Turner, 2022).
   2.2.10 MySQL
   MySQL adalah sistem basis data open-source yang menjadi standar industri karena kecepatan dan keandalannya. Sistem ini berfungsi sebagai penyimpan data utama bagi berbagai platform, mulai dari raksasa internet hingga aplikasi kecil. Keunggulan MySQL terletak pada fleksibilitasnya yang dapat diandalkan baik untuk aplikasi berbasis web maupun non-web, serta kemampuannya menjaga stabilitas performa dalam berbagai ukuran data (Dyer, 2015).
   2.2.11 TensorFlow.js & MediaPipe Face Mesh
   Sasaki (2019) mendefinisikan TensorFlow.js sebagai framework yang memungkinkan pengembang untuk menciptakan aplikasi machine learning berkinerja tinggi yang berjalan secara lancar di dalam peramban web. Framework ini memfasilitasi penggunaan akselerasi perangkat keras (seperti GPU melalui WebGL) secara langsung dari API peramban standar, tanpa memerlukan modifikasi khusus pada sisi klien (Sasaki, 2019). MediaPipe Face Mesh adalah solusi geometri wajah yang memperkirakan 468 koordinat landmark 3D wajah secara real-time pada perangkat seluler maupun web. Teknologi ini memungkinkan analisis gerakan kepala dan mata tanpa memerlukan perangkat keras khusus selain webcam standar, yang digunakan dalam penelitian ini untuk mendeteksi perilaku menoleh atau menunduk (Bimantoro et al., 2024).
   2.2.12 UML (Unified Modeling Language)
   UML (Unified Modeling Language) adalah standar bahasa pemodelan yang digunakan secara luas dalam industri untuk mendefinisikan kebutuhan, menganalisis, merancang, serta menggambarkan arsitektur sistem berbasis objek. Kehadiran UML didorong oleh kebutuhan akan visualisasi yang mampu menspesifikasikan, membangun, dan mendokumentasikan sistem perangkat lunak yang semakin kompleks. UML merupakan hasil penyatuan berbagai metode berorientasi objek yang sebelumnya terpisah, seperti OOSE (Object-Oriented Software Engineering) karya Grady Booch, OMT (Object Modelling Technique) karya James Rumbaugh, dan metode OOSE karya Ivar Jacobson. Standarisasi ini dikelola oleh Object Management Group (OMG) untuk menciptakan bahasa pemodelan yang seragam dan dapat diterima secara global (Hasanah & Untari, 2020).
   2.2.13 Black Box Testing
   Black Box Testing, yang juga dikenal sebagai behavioral testing atau pengujian fungsional, didefinisikan sebagai metode pengujian perangkat lunak yang berfokus pada persyaratan fungsional tanpa mempertimbangkan struktur internal program. Dalam metode ini, penguji hanya mengetahui input yang diberikan ke sistem dan output yang diharapkan, tanpa menganalisis kode program yang menghasilkan output tersebut. Pendekatan ini memungkinkan perekayasa perangkat lunak untuk merancang skenario pengujian yang memvalidasi seluruh persyaratan fungsional. Black Box Testing bukan merupakan pengganti metode white-box, melainkan pendekatan komplementer yang efektif untuk mendeteksi jenis kesalahan yang berbeda, seperti fungsi yang hilang atau tidak benar, kesalahan antarmuka, kesalahan pada struktur data, hingga masalah akses basis data eksternal (Gupta, 2010).
   2.2.14 Algoritma Fisher-Yates Shuffle
   Algoritma Fisher-Yates Shuffle, atau yang dikenal sebagai Algorithm P dalam literatur Knuth, adalah prosedur pengacakan yang efisien untuk komputer. Algoritma ini mentransformasikan urutan awal (X1, …, Xt) menjadi permutasi acak dengan melakukan pertukaran elemen secara in-place. Dalam setiap iterasinya, algoritma memilih indeks acak k dalam rentang elemen yang belum diacak, lalu menukar elemen pada indeks tersebut dengan elemen terakhir dari rentang tersebut (Xj). Prosedur ini pertama kali dipublikasikan oleh Fisher dan Yates pada tahun 1938 dan kemudian diadaptasi untuk komputasi oleh Durstenfeld pada tahun 1964 (Knuth, 1997).
   2.2.15 Use Case Diagram
   Use Case Diagram didefinisikan sebagai teknik pemodelan yang digunakan untuk mendokumentasikan persyaratan fungsional dan menggambarkan fungsionalitas yang diharapkan dari sebuah sistem. Fokus utama diagram ini adalah pada aspek 'apa' yang dilakukan oleh sistem, tanpa mendetailkan 'bagaimana' mekanisme teknisnya bekerja. Sebuah use case merepresentasikan interaksi spesifik antara sistem dan aktor (baik manusia maupun mesin) dalam menyelesaikan suatu pekerjaan. Selain membantu dalam analisis kebutuhan (requirement analysis), diagram ini juga berfungsi vital sebagai alat komunikasi desain dengan klien serta acuan dasar dalam perancangan test case (Hasanah & Untari, 2020). Contoh symbol-simbol Use Case Diagram ada di Gambar 2.1 berikut.

Gambar 2.1 Simbol-Simbol Use Case Diagram
2.2.16 Activity Diagram
Activity Diagram atau diagram aktivitas adalah diagram yang menggambarkan berbagai aliran aktivitas (workflow) dalam sistem yang sedang dirancang. Diagram ini memvisualisasikan bagaimana masing-masing alir bermula, keputusan (decision) yang mungkin terjadi di tengah proses, hingga bagaimana aktivitas tersebut berakhir. Berbeda dengan diagram yang menggambarkan perilaku internal sistem secara teknis dan eksak, Activity Diagram lebih berfokus pada penggambaran proses-proses dan jalur aktivitas dari level atas secara umum. Diagram ini dibuat berdasarkan sebuah Use Case dan memiliki struktur yang mirip dengan flowchart atau Data Flow Diagram (DFD) pada perancangan terstruktur. Selain menggambarkan proses sekuensial, diagram ini juga mampu menggambarkan proses paralel yang mungkin terjadi dalam beberapa eksekusi (Hasanah & Untari, 2020). Dibawah ini Gambar 2.2 symbol-simbol Activity Diagram.

Gambar 2.2 Simbol-Simbol Activity Diagram
2.2.17 Entity Relationship Diagram
Entity Relationship Diagram (ERD) didefinisikan sebagai metode pemodelan awal yang paling umum digunakan dalam perancangan basis data relasional. ERD merupakan diagram visual yang menggunakan notasi simbolis untuk mengidentifikasi tipe entitas, atribut yang menyertainya, serta menjelaskan hubungan (relasi) antar entitas tersebut. Sebagai sebuah model jaringan data yang abstrak, fokus utama ERD adalah menggambarkan struktur data dan keterhubungan antar data di dalam sistem (Hasanah & Untari, 2020).
2.2.18 www

BAB III ANALISA DAN PERANCANGAN
3.1 Analisis Permasalahan
Analisis permasalahan dilakukan guna mengetahui kebutuhan sistem keamanan baru yang akan diintegrasikan ke dalam platform E-Learning EduGames. Pada tahap ini, dipelajari bagaimana alur pengerjaan kuis yang sedang berjalan saat ini untuk mengidentifikasi celah keamanan yang ada.
Saat ini, proses pelaksanaan kuis di EduGames berjalan dengan prosedur standar: Instructor membuat bank soal dan mengatur waktu ujian, kemudian Student mengerjakan kuis tersebut melalui peramban (browser), dan sistem memberikan penilaian otomatis (auto-grading) setelah kuis disubmit. Meskipun sistem ini efisien secara operasional, namun memiliki kelemahan mendasar dalam hal pengawasan (proctoring). Tidak adanya mekanisme pemantauan aktivitas Student selama ujian berlangsung membuat sistem sangat rentan terhadap tindakan kecurangan akademik.
Masalah utama yang ditemukan adalah kemudahan Student untuk melakukan kecurangan digital maupun fisik tanpa terdeteksi. Pertama, Student dapat dengan mudah berpindah tab (tab switching) atau membuka aplikasi lain untuk mencari jawaban di internet. Kedua, karena urutan soal yang statis, Student dapat dengan mudah berbagi jawaban dengan rekannya. Ketiga, tidak adanya pengawasan visual memungkinkan terjadinya joki ujian atau kolusi di mana Student bisa bertanya kepada orang lain di sekitarnya. Akibatnya, skor yang dihasilkan sistem saat ini seringkali tidak mencerminkan kompetensi Student yang sebenarnya, sehingga menurunkan kredibilitas sertifikat kelulusan yang dikeluarkan oleh EduGames.
3.2 Analisis Pemecan Masalah
Berdasarkan permasalahan rendahnya integritas pada sistem kuis yang ada, maka diusulkan suatu rancangan Sistem Keamanan Kuis Online yang terintegrasi langsung dengan platform EduGames. Solusi ini berfokus pada pengembangan fitur deteksi dan pencegahan kecurangan berbasis web yang dapat diatur oleh Admin atau Instructor.
Pemecahan masalah dilakukan dengan mengimplementasikan tiga fitur keamanan utama. Pertama, untuk mencegah penyontekkan antar siswa, diterapkan Algoritma Fisher-Yates Shuffle yang akan mengacak urutan soal secara unik bagi setiap Student. Kedua, untuk menangani kecurangan pencarian jawaban daring, diterapkan fitur Deteksi Perpindahan Tab menggunakan Page Visibility API yang akan mencatat aktivitas Student saat meninggalkan halaman ujian. Ketiga, untuk pengawasan visual, diterapkan fitur Deteksi Kamera menggunakan teknologi TensorFlow.js dan MediaPipe Face Mesh untuk mendeteksi perilaku mencurigakan seperti menoleh atau menunduk.
Sistem yang diusulkan ini tidak hanya sekadar mendeteksi, tetapi juga memberikan output berupa Laporan Integritas bertingkat (Per Siswa, Per Kuis, dan Per Kursus). Laporan ini akan menyajikan data pelanggaran dan bukti deteksi (screenshot) kepada Instructor sebagai dasar pendukung keputusan dalam memberikan nilai akhir atau revisi skor. Sistem ini akan dibangun menggunakan framework Laravel di sisi backend untuk manajemen data dan logika bisnis, serta JavaScript di sisi frontend untuk menangani proses deteksi real-time di peramban pengguna.
3.2.1 Metode Pengambilan Data (user requirement)
Pengambilan data dilakukan untuk mendapatkan informasi kebutuhan fungsional dan non-fungsional yang diperlukan demi mencapai tujuan penelitian, yaitu membangun sistem keamanan kuis online. Dalam penelitian ini, pengambilan data dilakukan di Wahana Media Digital (EduGames). Metode pengambilan data dilakukan sebagai berikut:

1. Observasi
   Observasi (Observation) Observasi dilakukan dengan cara pengamatan langsung terhadap sistem kuis yang sedang berjalan di platform E-Learning EduGames saat ini. Penulis mengamati alur pengerjaan kuis oleh siswa untuk mengidentifikasi celah keamanan yang memungkinkan terjadinya kecurangan, seperti kemudahan berpindah tab (tab switching) tanpa terdeteksi dan tidak adanya pengawasan visual saat ujian berlangsung. Pengamatan ini bertujuan untuk memetakan kelemahan sistem lama yang akan diperbaiki oleh sistem baru.
2. Wawancara (Interview)
   Pengumpulan data melalui wawancara melibatkan tanya jawab antara penulis dengan pihak pengelola atau instruktur di Wahana Media Digital. Tujuannya adalah untuk menggali kebutuhan pengguna (user requirement) secara mendalam, seperti menentukan parameter pelanggaran apa saja yang perlu dicatat dalam Laporan Integritas, bagaimana format laporan yang diinginkan instruktur, serta mekanisme revisi skor yang dibutuhkan.
3. Studi Literatur
   Studi literatur dilakukan untuk mengumpulkan landasan teori dan referensi teknis yang diperlukan dalam perancangan sistem. Proses ini melibatkan pencarian referensi dari buku dan jurnal ilmiah terkait:
   • Algoritma Fisher-Yates Shuffle untuk pengacakan soal.
   • Teknologi Computer Vision menggunakan MediaPipe Face Mesh dan TensorFlow.js untuk deteksi kamera.
   • Teknik Javascript untuk deteksi perpindahan tab.
   • Pengembangan sistem berbasis framework Laravel. Referensi yang diperoleh akan menjadi acuan utama dalam menyusun solusi teknis pembangunan sistem.
   3.2.2 Metode Pengembangan Sistem
   Metode pengembangan sistem yang digunakan dalam penelitian ini adalah metode Waterfall. Metode Waterfall merupakan model pengembangan sistem yang dilakukan secara berurutan dan linier, di mana setiap tahapan harus diselesaikan terlebih dahulu sebelum melangkah ke tahapan berikutnya. Tahapan metode Waterfall terdiri dari lima fase utama, yaitu analisis kebutuhan, desain sistem, penulisan kode program, pengujian program, serta penerapan program dan pemeliharaan. Berikut adalah rincian tahapan yang dilakukan:

Gambar 3.3 Metode Pengembanagn Sistem
Pada gambar 3.3 menggambarkan metode pengembangan sistem waterfall, secara garis besar metode ini mempunyai 5 tahapan, yaitu: analisa kebutuhan, desain sistem, penulisan kode program, pengujian sistem, dan implementasi. Detail dan penjelasan mengenai tahapan metode waterfall sebagai berikut.

1. Analisa Kebutuhan
   Analisis Kebutuhan (Requirements Analysis) Tahap ini merupakan langkah awal yang krusial untuk memahami kebutuhan fungsional dan non-fungsional dari sistem keamanan yang akan dibangun. Pada tahap ini, penulis menganalisis kebutuhan fitur Deteksi Perpindahan Tab, Pengacakan Soal, dan Deteksi Kamera berdasarkan hasil observasi dan studi literatur. Data yang diperoleh diolah menjadi spesifikasi kebutuhan perangkat lunak yang akan menjadi acuan dalam pengembangan fitur.
2. Desain Sistem
   Desain Sistem (System Design) Berdasarkan spesifikasi kebutuhan yang telah didefinisikan, tahap selanjutnya adalah perancangan sistem. Penulis merancang arsitektur sistem keamanan yang akan diintegrasikan ke dalam framework Laravel EduGames. Desain ini mencakup perancangan basis data (Database Design) untuk menyimpan log pelanggaran, perancangan antarmuka (User Interface) untuk halaman ujian dan laporan integritas, serta perancangan alur logika sistem menggunakan Unified Modeling Language (UML) seperti Use Case Diagram, Activity Diagram, dan Class Diagram.
3. Penulisan Kode Program
   Penulisan Kode Program (Implementation) Pada tahap ini, desain sistem diterjemahkan ke dalam bahasa pemrograman yang dapat dipahami oleh komputer. Penulis melakukan pengkodean (coding) menggunakan bahasa PHP dengan framework Laravel untuk sisi backend dan JavaScript (termasuk penggunaan library TensorFlow.js dan MediaPipe) untuk sisi frontend. Implementasi difokuskan pada pembangunan logika deteksi keamanan dan mekanisme pelaporan yang telah dirancang sebelumnya.
4. Pengujian Program
   Pengujian Program (Testing) Setelah kode program selesai ditulis, dilakukan pengujian untuk memastikan setiap unit program berjalan sesuai dengan fungsinya. Pengujian dilakukan menggunakan metode Black Box Testing untuk memvalidasi fungsionalitas fitur, seperti memastikan algoritma pengacakan soal bekerja dengan benar, deteksi kamera mampu menangkap gerakan mencurigakan, dan sistem berhasil mencatat perpindahan tab. Tahap ini bertujuan untuk mengidentifikasi bug atau kesalahan logika sebelum sistem diterapkan.
5. Penerapan Program dan Pemeliharaan
   Penerapan Program dan Pemeliharaan (Deployment & Maintenance) Tahap terakhir adalah penerapan sistem yang telah lulus uji ke dalam lingkungan produksi EduGames. Setelah sistem berjalan, dilakukan pemeliharaan untuk memastikan sistem tetap beroperasi secara optimal. Pemeliharaan mencakup perbaikan kesalahan yang mungkin baru terdeteksi setelah penggunaan nyata, serta penyesuaian atau penambahan fitur kecil jika diperlukan untuk meningkatkan kinerja sistem keamanan.
   3.2.3 Analisis Kebutuhan Sistem (fungsional dan non fungsional sistem)
   Analisis kebutuhan sistem bertujuan untuk mengidentifikasi kebutuhan spesifik yang diperlukan dalam membangun sistem keamanan kuis online. Kebutuhan ini dibagi menjadi dua kategori, yaitu kebutuhan fungsional dan kebutuhan non-fungsional.
   a) Kebutuhan Fungsional
   • Instructor (Instruktur)
    Instruktur dapat mengaktifkan atau menonaktifkan fitur keamanan (Deteksi Perpindahan Tab, Pengacakan Soal, Deteksi Kamera) saat membuat kuis.
    Instruktur dapat melihat Laporan Integritas per Siswa yang berisi skor, jumlah perpindahan tab, dan bukti deteksi kamera.
    Instruktur dapat melihat Rekapitulasi Laporan Integritas per Kuis untuk memantau integritas satu kelas secara keseluruhan.
    Instruktur dapat melihat Rekapitulasi Laporan Integritas per Kursus untuk melihat riwayat integritas siswa di seluruh kuis dalam satu kursus.
    Instruktur dapat melakukan revisi skor siswa berdasarkan keputusan pribadi setelah melihat data pelanggaran yang ditemukan di laporan integritas.
   • Student
    Siswa dapat memberikan izin akses kamera sebelum memulai ujian.
    Siswa akan mendapatkan peringatan sistem jika terdeteksi melakukan perpindahan tab atau aplikasi lain.
    Siswa mengerjakan soal yang telah diacak urutannya secara otomatis oleh sistem.
    Siswa dimonitor oleh kamera secara otomatis selama pengerjaan kuis untuk mendeteksi indikasi kecurangan (menoleh/menunduk).
   b) Kebutuhan Non-Fungsional
   Analisis kebutuhan non-fungsional mendefinisikan batasan layanan dan spesifikasi teknis yang diperlukan agar sistem dapat berjalan dengan baik.
   • Kebutuhan Perangkat Keras (Hardware) Spesifikasi perangkat keras minimal yang digunakan untuk pengembangan dan pengujian sistem adalah sebagai berikut:
    Prosesor: Minimal Intel Core i3 atau setara (Disarankan i5 ke atas untuk menjalankan model ML dengan lancar).
    RAM: Minimal 4 GB (Disarankan 8 GB).
    Penyimpanan: SSD 256 GB.
    Perangkat Tambahan: Webcam yang berfungsi dengan baik (untuk pengujian fitur deteksi kamera).
   • Kebutuhan Perangkat Lunak (Software) Perangkat lunak yang digunakan dalam pengembangan sistem ini adalah:
    Sistem Operasi: Windows 10/11 atau macOS.
    Web Browser: Google Chrome (Versi terbaru disarankan untuk kompatibilitas MediaPipe).
    Web Server: XAMPP / Laragon (Apache, MySQL, PHP).
    Text Editor: Visual Studio Code.
    Framework: Laravel (Backend).
    Library: TensorFlow.js dan MediaPipe Face Mesh (Frontend).
    Database: MySQL.
   3.3 Perancangan Sistem
   Perancangan sistem dilakukan untuk memberikan gambaran teknis yang mendalam mengenai arsitektur dan alur kerja sistem keamanan kuis yang akan dibangun pada platform E-Learning EduGames. Tahapan perancangan ini mencakup identifikasi kebutuhan sistem melalui Use Case Diagram, Activity Diagram, dan Class Diagram, serta perancangan basis data dan desain antarmuka (User Interface) secara terperinci. Hal ini bertujuan agar proses implementasi fitur Deteksi Perpindahan Tab, Pengacakan Soal, dan Deteksi Kamera dapat berjalan dengan baik dan terstruktur.
   3.3.1 Use Case Diagram
   Use Case Diagram berfungsi untuk menggambarkan fungsionalitas yang diharapkan dari sistem keamanan yang akan dibangun, serta memvisualisasikan bagaimana interaksi antara pengguna (aktor) dengan sistem tersebut. Pada sistem ini, terdapat dua aktor utama yang terlibat, yaitu Instructor (Dosen/Pengajar) dan Student (Siswa).
   Instructor memiliki hak akses untuk mengelola konfigurasi keamanan kuis dan memantau hasil integritas siswa melalui laporan bertingkat. Sementara itu, Student berinteraksi dengan sistem saat mengerjakan kuis yang diawasi oleh fitur deteksi otomatis (tab switching dan kamera). Rancangan Use Case Diagram untuk sistem keamanan kuis pada EduGames dapat dilihat pada Gambar 3.2.

Gambar 3.4 Gambar Use Case Diagram
Berdasarkan use case diagram yang telah dirancang, dapat disimpulkan bahwa sistem ini mempunyai definisi diantaranya sebagai berikut:

1. Definisi Aktor
   Berikut merupakan definisi aktor yang terlibat dalam sistem keamanan kuis online pada platform EduGames, yang ditunjukkan pada Tabel 3.1 berikut.
   Tabel 3.2 Tabel Definisi Aktor
   No. Aktor Deskripsi
   1 Instructor Pengguna yang memiliki hak akses untuk mengelola konten kuis dan melakukan evaluasi. Instructor bertanggung jawab untuk membuat kuis, mengatur opsi keamanan (seperti deteksi kamera dan pengacakan soal), serta memantau integritas akademik siswa melalui laporan deteksi kecurangan untuk menentukan validitas nilai.
   2 Student Pengguna yang terdaftar sebagai peserta dalam suatu kursus. Student memiliki akses untuk mengerjakan kuis yang telah disediakan, di mana aktivitas pengerjaannya akan dipantau oleh sistem keamanan (proctoring) sebelum akhirnya dapat melihat hasil nilai kuis mereka.

2. Definisi Use Case
   Berikut merupakan penjelasan definisi use case yang terlibat dalam sistem keamanan kuis online pada platform EduGames, yang ditunjukkan pada Tabel 3.2 berikut.
   No. Use Case Deskripsi
   1 Mengerjakan Kuis Proses di mana Student melakukan pengerjaan soal ujian online. Pada proses ini, sistem keamanan akan aktif secara otomatis untuk mendeteksi perpindahan tab dan memantau aktivitas wajah melalui kamera (jika diaktifkan oleh Instructor).
   2 Melihat Hasil Kuis Proses di mana Student dan Instructor dapat melihat skor atau nilai akhir yang diperoleh setelah kuis selesai dikerjakan.
   3 Mengelola Kuis Proses utama di mana Instructor dapat membuat kuis baru, mengedit soal, mengatur waktu ujian, atau menghapus kuis yang sudah ada di dalam kursus.
   4 Mengatur Opsi Keamanan Kuis Proses tambahan (extension) dari mengelola kuis, di mana Instructor dapat mengaktifkan atau menonaktifkan fitur keamanan spesifik, seperti toggle untuk Deteksi Perpindahan Tab, Pengacakan Soal (Fisher-Yates), dan Deteksi Kamera (MediaPipe).
   5 Melihat Laporan Integritas Hasil Kuis per Percobaan Kuis Proses di mana Instructor melihat detail laporan keamanan untuk satu percobaan tertentu. Laporan ini berisi skor asli, jumlah perpindahan tab, dan galeri bukti deteksi (flag) kamera saat terjadi indikasi kecurangan.
   6 Melakukan Revisi Skor Proses tambahan (extension) dari melihat laporan integritas, di mana Instructor dapat mengubah atau membatalkan nilai Student berdasarkan bukti kecurangan yang ditemukan dalam laporan integritas.
   7 Melihat Rekapitulasi Laporan Integritas per Kuis Proses di mana Instructor melihat ringkasan data integritas seluruh siswa dalam satu judul kuis (misal: "Kuis 1"). Data disajikan dalam bentuk tabel rekapitulasi untuk memudahkan pemantauan kelas secara keseluruhan.
   8 Melihat Rekapitulasi Laporan Integritas per Kursus Proses di mana Instructor melihat akumulasi data integritas seluruh siswa dari semua kuis yang ada di dalam satu mata kuliah/kursus penuh.

3.3.2 Activity Diagram
Activity diagram adalah bagian dari representasi sistem secara fungsional yang menjelaskan proses logika atau fungsi yang diimplementasikan melalui kode program. Diagram ini memodelkan peristiwa-peristiwa yang terjadi dalam suatu use case dan digunakan untuk menggambarkan aspek dinamis dari sistem. Secara dasar, activity diagram memiliki struktur yang mirip dengan flowchart dalam perancangan sistem yang terstruktur. Diagram ini dibuat berdasarkan sebuah use case dan menggambarkan aliran kerja atau aktivitas dari suatu sistem, proses bisnis, atau menu yang terdapat dalam perangkat lunak. Penting untuk dicatat bahwa activity diagram menggambarkan aktivitas sistem, bukan tindakan yang dilakukan oleh aktor, sehingga fokusnya adalah pada aktivitas yang dapat dilakukan oleh sistem (Hasanah & Untari, 2020). Berikut adalah activity diagram untuk sistem keamanan kuis pada EduGames.

-   Diagram Activity Mengerjakan Kuis
    Activity diagram ini menggambarkan alur utama proses pengerjaan kuis oleh Student, dimulai dari pemilihan materi hingga penyelesaian ujian. Alur ini mengintegrasikan fitur keamanan sebagai prasyarat utama. Sebelum kuis dimulai, sistem melakukan validasi jadwal serta sisa kesempatan (attempt), dilanjutkan dengan permintaan izin akses kamera kepada Student. Apabila izin diberikan, sistem akan membuat sesi kuis baru dan menerapkan algoritma Fisher-Yates Shuffle untuk mengacak urutan soal secara unik bagi setiap peserta.
    Selama fase iterasi pengerjaan soal, sistem menjalankan proses pemantauan secara paralel (fork). Sistem tidak hanya menyimpan jawaban sementara Student, tetapi juga secara aktif menjalankan fitur keamanan di latar belakang, yaitu Deteksi Wajah dan Deteksi Perpindahan Tab. Jika ditemukan indikasi kecurangan, sistem akan mencatatnya ke dalam log pelanggaran dan mengambil bukti gambar (snapshot) sebagai barang bukti. Proses berakhir ketika Student melakukan konfirmasi penyelesaian kuis, di mana sistem akan menghitung skor akhir, menyimpan status kelulusan, dan menutup sesi pengerjaan. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.5.

Gambar 3.5 Diagram Activity Mengerjakan Kuis

-   Diagram Activity Melihat Hasil Kuis
    Activity diagram ini menggambarkan proses peninjauan hasil evaluasi yang melibatkan dua aktor utama, yaitu Student dan Instructor, dengan sistem sebagai fasilitator data. Alur diagram ini memvisualisasikan bagaimana hasil kuis diakses dari dua perspektif yang berbeda namun saling terhubung melalui Laporan Integritas.
    Di sisi Student, proses dimulai dari menu kuis, di mana mereka dapat melihat riwayat percobaan (attempt). Jika status kuis telah selesai, sistem akan menampilkan skor akhir beserta notifikasi jika terdapat indikasi kecurangan yang terdeteksi selama pengerjaan. Di sisi Instructor, proses dimulai dari menu "Lihat Nilai" pada daftar pelajaran (lesson). Sistem akan menyajikan daftar seluruh siswa beserta status kelulusannya. Instructor dapat meninjau lebih dalam dengan membuka modal riwayat dan memeriksa jawaban detail setiap percobaan. Pada tahap ini, sistem akan menampilkan "Laporan Integritas" yang berisi bukti visual kecurangan (jika ada), yang memungkinkan Instructor untuk melakukan verifikasi lebih lanjut sebelum memvalidasi nilai akhir. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.6.
    .

Gambar 3.6 Diagram Activity Melihat Hasil Kuis

-   Diagram Activity Mengelola Kuis
    Activity diagram ini menggambarkan alur kerja Instructor dalam melakukan manajemen data kuis, yang meliputi tiga fungsi utama: penambahan kuis baru (Create), pembaruan data kuis (Update), dan penghapusan kuis (Delete).
    Aktivitas dimulai dari menu "Kelola Kursus" pada dashboard, di mana sistem menampilkan daftar modul dan pelajaran yang tersedia. Instructor memiliki fleksibilitas untuk memilih salah satu dari tiga jalur aktivitas secara paralel: menambah kuis baru dengan mengisi formulir lengkap (judul, deskripsi, durasi, passing grade), mengedit kuis yang sudah ada untuk memperbarui parameter tertentu, atau menghapus kuis beserta relasi soalnya dari sistem. Setiap perubahan data yang dilakukan akan divalidasi oleh sistem sebelum disimpan ke dalam basis data, dan sistem akan memberikan umpan balik berupa pesan sukses serta menampilkan daftar pelajaran yang telah diperbarui. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.7.

Gambar 3.7 Diagram Activity Mengelola Kuis

-   Diagram Activity Mengatur Opsi Keamanan Kuis
    Activity diagram ini mendetailkan proses konfigurasi fitur keamanan yang dilakukan oleh Instructor saat membuat atau mengedit kuis. Proses ini terintegrasi langsung di dalam formulir kuis.
    Saat Instructor membuka formulir kuis, sistem akan menampilkan bagian khusus "Opsi Keamanan". Pada tahap ini, Instructor diberikan fleksibilitas penuh untuk mengaktifkan kombinasi fitur keamanan sesuai kebutuhan melalui mekanisme pemilihan paralel (fork). Instructor dapat memilih untuk mengaktifkan Deteksi Kamera untuk pengawasan visual, Deteksi Perpindahan Tab untuk mencegah pencarian jawaban daring, dan/atau Pengacakan Soal untuk meminimalisir kerja sama antar siswa. Setelah konfigurasi dipilih dan tombol simpan ditekan, sistem akan memvalidasi pilihan tersebut dan menyimpan status aktif/non-aktif setiap fitur ke dalam database kuis. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.8.

Gambar 3.8 Diagram Activity Mengatur Opsi Keamanan Kuis

-   Diagram Activity Melihat Laporan Integritas per Percobaan Kuis
    Activity diagram ini mendetailkan proses verifikasi integritas akademik yang dilakukan oleh Instructor terhadap satu sesi pengerjaan kuis siswa tertentu. Fitur ini diakses melalui modal riwayat percobaan (attempt history).
    Setelah Instructor memilih untuk melihat laporan integritas pada suatu percobaan, sistem akan melakukan pencarian data pada tabel log pemantauan (monitoring logs) dan mengambil file bukti visual dari penyimpanan. Jika data pelanggaran ditemukan, sistem akan menampilkan halaman detail yang berisi rekapitulasi jumlah perpindahan tab dan galeri foto wajah yang terindikasi mencurigakan. Instructor kemudian menganalisis bukti tersebut secara manual. Apabila bukti dianggap valid sebagai kecurangan, Instructor dapat melanjutkan ke proses revisi skor. Namun, jika bukti dianggap tidak cukup kuat atau tidak ada pelanggaran yang terdeteksi, Instructor dapat kembali ke menu sebelumnya. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.9.

Gambar 3.9 Diagram Activity Melihat Laporan Integritas per Percobaan

-   Diagram Activity Melakukan Revisi Skor
    Activity diagram ini menggambarkan prosedur pemberian nilai revisi oleh Instructor sebagai tindak lanjut dari analisis laporan integritas. Proses ini merupakan bentuk intervensi manual terhadap penilaian otomatis sistem.
    Aktivitas dimulai ketika Instructor menekan tombol "Revisi Skor" pada halaman Laporan Integritas. Sistem akan menampilkan formulir (modal) yang meminta Instructor untuk memasukkan nilai baru (sebagai pengganti nilai otomatis) dan alasan revisi (sebagai catatan log). Setelah data diinput dan disubmit, sistem akan melakukan validasi untuk memastikan nilai berada dalam rentang 0-100. Jika valid, sistem akan memperbarui data skor pada tabel percobaan kuis (quiz_attempts), menyimpan catatan alasan, dan menampilkan pesan sukses beserta nilai terbaru yang telah diperbarui. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.10.
    .

Gambar 3.10 Activity Diagram Melakukan Revisi Skor

-   Diagram Activity Melihat Rekapitulasi Laporan per Kuis
    Activity diagram ini menggambarkan proses pemantauan integritas akademik pada tingkat kelas atau kuis secara keseluruhan. Fitur ini dirancang untuk memberikan gambaran umum (overview) kepada Instructor mengenai tingkat kejujuran peserta dalam satu judul kuis tertentu.
    Aktivitas dimulai ketika Instructor memilih menu rekapitulasi pada daftar kuis. Sistem kemudian menjalankan proses agregasi data di latar belakang dengan mengumpulkan seluruh sesi ujian (quiz attempts) yang terkait dengan kuis tersebut. Sistem akan menghitung total pelanggaran yang terdeteksi, seperti jumlah perpindahan tab dan flag kamera dari setiap peserta. Jika data tersedia, sistem akan menyajikan dashboard statistik yang menampilkan ringkasan performa integritas kelas, termasuk daftar siswa dengan indikasi kecurangan tertinggi. Namun, jika belum ada siswa yang mengerjakan kuis, sistem akan memberikan informasi bahwa data partisipan belum tersedia. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.11.

Gambar 3.11 Activity Diagram Melihat Rekapitulasi Laporan per Kuis

-   Diagram Activity Melihat Rekapiturlasi Laporan per Kursus
    Activity diagram ini menggambarkan proses pelaporan tingkat tinggi yang merangkum integritas akademik untuk satu mata kuliah atau kursus secara keseluruhan. Fitur ini dirancang untuk memberikan wawasan jangka panjang kepada Instructor mengenai pola perilaku peserta didik selama mengikuti kursus.
    Aktivitas dimulai dari halaman manajemen kursus utama, di mana Instructor menekan tombol "Rekapitulasi Laporan Integritas". Sistem kemudian menjalankan proses komputasi yang kompleks di latar belakang: mengambil daftar seluruh kuis yang ada dalam kursus tersebut, lalu melakukan kueri dan akumulasi data pelanggaran (tab switching dan flag kamera) dari setiap kuis untuk setiap siswa. Jika data tersedia, sistem akan menyajikan dashboard analitik yang menampilkan profil risiko integritas siswa, seperti total akumulasi pelanggaran selama satu semester. Hal ini memungkinkan Instructor untuk melakukan evaluasi menyeluruh terhadap kredibilitas kelulusan peserta. Alur lengkap aktivitas ini dapat dilihat pada Gambar 3.12.

Gambar 3.12 Activity Diagram Melihat Rekapitulasi Laporan per Kursus
3.3.3 Relasi antar Tabel
3.3.4 Arsitektur Diagram
3.3.5 Antarmuka Pengguna (User Interface)
3.4 Pengujian Sistem
BAB IV JADWAL KEGIATAN
4.1 Jadwal
4.2 Pembagian Tugas
DAFTAR PUSTAKA
Abdullloh, R. (2018). 7 IN 1PEMROGRAMAN WEB UNTUK PEMULA Cara cepat dan efektif menjadi web programmer. PT Elex Media Komputindo.
Agustinus, J. T., & Engel, M. M. (2024). REAL TIME ONLINE EXAM PROCTORING SYSTEM IN HIGHER EDUCATION SISTEM PENGAWASAN UJIAN ONLINE SECARA REAL TIME PADA. 4(6), 1575–1587.
Anam, S., & Sifaunajah, A. (2022). Design and Build a Web-based Learning Management System Using the Laravel Framework. 13–18.
Anam, S., Sifaunajah, A., Wahab, K. A., & Jombang, H. (2022). Design and Build a Web-based Learning Management System Using the Laravel Framework. Prosiding SEMNAS INOTEK (Seminar Nasional Inovasi Teknologi), 6, 013–018.
Berman, P. (2006). E-Learning Concepts and Techniques. Institute for Interactive Technologies.
Bimantoro, E., Hidayattullah, M. F., & Af, I. (2024). Learning Management System ( LMS ) Pada Kursus Online Berbasis Deteksi Kecurangan Ujian Menggunakan Model Mediapipe Face Mesh Learning Management System ( LMS ) in Online Courses Based on Exam Cheating Detection Using the Mediapipe Face Mesh Model. 2, 268–278. https://doi.org/10.26798/jiko.v8i2.1167
Dyer, R. J. T. (2015). Learning MySQL and MariaDB Heading in the Right Direction with MySQL and MariaDB (A. Oram (ed.); 1st Editio). O’Reilly Media. http://bit.ly/lrng_mysql_and_mariadb
Gupta, B. B. A. S. P. T. M. (2010). SOFTWARE ENGINEERING & TESTING. Jones & Barlett Learning.
Hasanah, F. N., & Untari, R. S. (2020). Rekayasa Perangkat Lunak. In M. K. Mohammad Suryawinata, S.Pd. & Design (Eds.), Buku Ajar Rekayasa Perangkat Lunak (1st Editio). UMSIDA Press.
Jensen, J. S. (2022). The Missing Bootstrap 5 Guide (M. Dsouza (ed.); 1st editio). Packt Publishing Ltd. https://www.google.co.id/books/edition/The_Missing_Bootstrap_5_Guide/ydp-EAAAQBAJ?hl=en&gbpv=0
Knuth, D. E. (1997). The Art of Computer Programming (vol. 2\_ Seminumerical Algorithms) (3rd ed.) [Knuth 1997-11-14].pdf. In The Art of Computer Programming (2th edt). Addison Wesley Longman.
Lubis, M. F., Antoni, & Aulia, R. (2025). IMPLEMENTASI ALGORITMA FISHER-YATES SHUFFLE PADA SISTEM UJIAN ONLINE BERBASIS WEBSITE DI SMP SWASTA IR . H . DJUANDA TEBING. 2, 447–470.
Melani, I. (2023). Rancang Bangun Aplikasi Web untuk Platform E-Learning dengan Fitur Pembelajaran Interaktif dan Ujian Online. 3(4), 1–21.
Robbins, J. N. (2012). Learning Web Design, 4th Edition A Beginner’s Guide to HTML, CSS, JavaScript, and Web Graphics (M. Yarbrough (ed.); 4th Editio). O’Reilly Media.
Sasaki, K. (2019). Hands-On Machine Learning With TensorFlow.js (S. Rogers (ed.)). Packt Publishing.
Siregar, R. R., Nasution, K., & Haramaini, T. (2021). Aplikasi Ujian Online Untuk Siswa Sekolah Menengah Pertama Dengan Menggunakan Metode Rational Unified Process ( RUP ). 10, 33–41.
Technology, C. (2015). PHP QuickStart Guide The Simplified Beginner’s Guide To PHP. CreateSpace Independent Publishing Platform.
Turner, A. (2022). Laravel 9.x | PHP Learning Laravel with Easiest Way. Andy Turner.
