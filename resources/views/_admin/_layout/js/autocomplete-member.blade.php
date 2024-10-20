<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(function() {
        $("#loading-autocomplete").hide();
        $("#identity_no").keyup(function() {
            if (this.value == "") {
                $("#identity_no").removeClass('is-valid');
                $("#identity_no").removeClass('is-invalid');
            }
        });

        $("#identity_no").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "@php echo url('admin/member/api/search') @endphp",
                    dataType: "json",
                    data: {
                        term: request.term
                    },
                    success: function(data) {
                        if (data.length === 0) {
                            $("#identity_no").addClass('is-invalid');
                            $("#identity_no").removeClass('is-valid');
                            $("#nik").val("");
                        } else {
                            $("#identity_no").addClass('is-valid');
                            $("#identity_no").removeClass('is-invalid');
                        }
                        response($.map(data, function(item) {
                            return {
                                label: item.name + " (ID: " + item
                                    .identity_no + ")",
                                value: item.identity_no,
                                item: item
                            };
                        }));
                        $("#loading-autocomplete").hide();
                    },
                    error: function() {
                        $("#loading-autocomplete").hide();
                        $("#identity_no").addClass('is-invalid');
                    }
                });
            },
            select: function(event, ui) {
                $("#nik").val(ui.item.item.nik);
                $("#id_penduduk").val(ui.item.item.id);
            },
            search: function() {
                $("#identity_no").removeClass('is-invalid');
                $("#identity_no").removeClass('is-valid');
                $("#loading-autocomplete").show();
            },
            minLength: 3,
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append('<div><span class="ui-label-title" style="font-weight: 700">' + item.item.name +
                    "</span><br>ID: " + item
                    .item.identity_no + " - " + item.item.identity_type + "</div>")
                .appendTo(ul);
        };
    });
</script>
