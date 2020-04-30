{extend name="public/container"}
{block name="content"}

<div class="ibox-content order-info">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    工人基本信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <input type="hidden" value="{$to_examine.user_id}" id="uid" />
                        <div class="col-xs-12" >姓名: {$to_examine.name}</div>
                        <div class="col-xs-12">联系电话: {$to_examine.account}</div>
                        <div class="col-xs-12">地址: {$to_examine.address}</div>
                        <div class="col-xs-12" >身份证号: {$to_examine.id_card}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    身份信息
                </div>
                <div class="panel-body">
                    <div class="row show-grid">
                        <div class="col-xs-6" style="font-size: 18px;align-items: center;">身份证正面照片
                            <img style="cursor: pointer;width:210%" lay-event="open_image" src="{$to_examine.card_z}">
                        </div>
                        <div class="col-xs-7" style="font-size:18px;align-items: center;">身份证背面照片
                                <img style="cursor: pointer;width:175%" lay-event="open_image" src="{$to_examine.card_f}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="font-size: 18px;">
                    证书照片:
                    <div class="col-xs-6" >工人类别:
                        {if $to_examine['career_type'] eq 1}
                        水电工
                        {elseif $to_examine['career_type'] eq 2}
                        家电维修
                        {elseif $to_examine['career_type'] eq 3}
                        开锁匠
                        {elseif $to_examine['career_type'] eq 4}
                        瓦工
                        {elseif $to_examine['career_type'] eq 5}
                        油漆工
                        {else/}
                        {/if}
                    </div>
                </div>

                    <div class="panel-body">
                        <div class="row show-grid">
                    <div class="col-xs-6" >
                        <img style="cursor: pointer;width:210%" lay-event="open_image" src="{$to_examine.certificate}">
                    </div>
                    </div>
                </div>
            </div>
        </div>
            <button type="button" class="layui-btn layui-btn-xs" style="background-color: #0bb20c;width: 20%;height: 100%" id="ww" onclick="to_examine(2)">通过审核 </button>
            <button type="button" class="layui-btn layui-btn-xs " style="background-color: #9c3328;width: 20%;height: 100%" onclick="to_examine(3)">驳回 </button>
    </div>
</div>
<script src="{__FRAME_PATH}js/content.min.js?v=1.0.0"></script>
<script type="text/javascript">

    function to_examine(flag) {
        var uid = $("#uid").val();
        var url = "https://youcai.test.com/admin/workers.order/test.html";
        $.ajax({
            type: "post",
            url: "examine",
            data: 'uid=' + uid + '&flag=' + flag,
            dataType: 'json',
            async: false,
            success: function (msg) {
                if(msg.data == 1){
                    $eb.$swal('success',msg.msg);
                    parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                    $eb.closeModalFrame(window.name);


                }
                else if(msg.data ==0){
                    $eb.$swal('error',msg.msg);
                    parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                    $eb.closeModalFrame(window.name);

                }
            }
        });
    }
</script>

{/block}



