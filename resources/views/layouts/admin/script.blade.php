<script src="{{ asset('assets') }}/libs/jquery/jquery.min.js"></script>
<script src="{{ asset('assets') }}/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets') }}/libs/metismenu/metisMenu.min.js"></script>
<script src="{{ asset('assets') }}/libs/simplebar/simplebar.min.js"></script>
<script src="{{ asset('assets') }}/libs/node-waves/waves.min.js"></script>

<script src="https://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
{!! Toastr::message() !!}

<!-- apexcharts -->
<script src="{{ asset('assets') }}/libs/apexcharts/apexcharts.min.js"></script>

<!-- jquery.vectormap map -->
<script src="{{ asset('assets') }}/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="{{ asset('assets') }}/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-us-merc-en.js"></script>

<!-- Required datatable js -->
<script src="{{ asset('assets') }}/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets') }}/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<!-- Responsive examples -->
<script src="{{ asset('assets') }}/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('assets') }}/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script src="{{ asset('assets') }}/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="{{ asset('assets') }}/js/app.js"></script>

<!-- Required Select2 js -->
<script src="{{ asset('assets') }}/libs/select2/js/select2.min.js"></script>
<script src="{{ asset('assets') }}/js/pages/form-advanced.init.js"></script>
<script src="{{ asset('assets') }}/js/pages/form-validation.init.js"></script>

<script src="{{ asset('assets') }}/libs/sweetalert2/sweetalert2.min.js"></script>
<!-- Sweet alert init js-->
<script src="{{ asset('assets') }}/js/pages/sweet-alerts.init.js"></script>

<script>
    $(document).ready(function() {
        $(document).on('click', '#page-header-notifications-dropdown', function(e) {
            var url = "{{ route('admin.ajax.mark_as_read') }}";
            $.ajax({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                type: "get",
                url: url,
                success: function(response) {
                    $("#page-header-notifications-dropdown").load(' #page-header-notifications-dropdown > * ');
                },
                error: function(error) {
                    console.log(error);
                }
            });
        })
    })
</script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" integrity="sha384-2huaZvOR9iDzHqslqwpR87isEmrfxqyWOF7hr7BY6KG0+hVKLoEXMPUJw3ynWuhO" crossorigin="anonymous"></script>
{{--<script>--}}

{{--    const socket = io('http://127.0.0.1:3000');--}}

{{--    socket.on('connect', () => {--}}
{{--        console.log('Connected to Socket.io server');--}}
{{--    });--}}

{{--    socket.on('newUser', (userData) => {--}}
{{--        console.log('New user registered:', userData);--}}
{{--        // Update your admin dashboard UI to display the new user notification--}}
{{--    });--}}

{{--</script>--}}

<script>
    $(function() {
        let ip_address = '127.0.0.1';
        let socket_port = '3000';
        let socket = io(ip_address + ':' + socket_port);

        socket.on('connection');
        socket.on('notification',(data)=>{
            console.log(data);
            $(".notificationsIcon").load(" .notificationsIcon > *");
            // $("#page-header-notifications-dropdown").load("#page-header-notifications-dropdown * >")
            // $("#page-header-notifications-dropdown").load(`<span class="noti-dot"></span>`)
        });

        // let chatInput = $('#chatInput');
        //
        // chatInput.keypress(function(e) {
        //     let message = $(this).html();
        //     console.log(message);
        //     if(e.which === 13 && !e.shiftKey) {
        //         socket.emit('sendChatToServer', message);
        //         chatInput.html('');
        //         return false;
        //     }
        // });
        //
        // socket.on('sendChatToClient', (message) => {
        //     $('.chat-content ul').append(`<li>${message}</li>`);
        // });
    });
</script>
