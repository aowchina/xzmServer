$(function () { imgZoomInit(); });//模块初始化
/*
 * 图片放大展示

 */
function imgZoomInit(){
    $('.piclist li.pic').append(function(){
        ht = $(this).find('.in').html();
        return "<div class='original'>"+ht+"</div>";
    });

    $(".piclist li.pic .in img").each(function(i){
        var img = $(this);
        var realWidth ;//原始宽度
        var realHeight ;//原始高度
        var vs ;//图片宽高比

        realWidth = this.width;
        realHeight = this.height;
        vs = realWidth/realHeight;

        //缩略图比例230:142(约等于1.62)
        if(vs>=1.62){//横图则固定高度
            // $(img).css("width","auto").css("height","142px").css("marginLeft",115-(71*realWidth/realHeight)+"px");
            $(img).css("width","auto").css("height","auto").css("marginLeft","auto");
        }
        else{//竖图则固定宽度
            // $(img).css("width","auto").css("height","auto").css("marginTop",71-(115*realHeight/realWidth)+"px");
            $(img).css("width","auto").css("height","auto").css("marginTop","auto");
        }

        //图片放大水平垂直居中显示
        if(vs>=1){//横图或正方形
            if(realWidth < 100){//小图
                $(img).parent().parent().parent().find('.original img').height(50);
                $(img).parent().parent().parent().find('.original img').width('auto');
                $(img).parent().parent().parent().find('.original').css({
                    //此处需结合实际情况计算 左偏移：.original实际宽度的二分之一
                    marginLeft: function(){
                        return -(25*realWidth/realHeight)-6;
                        // return 'auto';
                    },
                    marginTop: function(){
                        return (10*realWidth/realHeight);
                    },
                    left:'50%'
                })
            }else{
                $(img).parent().parent().parent().find('.original img').height(260);
                $(img).parent().parent().parent().find('.original img').width('auto');
                $(img).parent().parent().parent().find('.original').css({
                    //此处需结合实际情况计算 左偏移：.original实际宽度的二分之一
                    marginLeft: function(){
                        return -(130*realWidth/realHeight)-6;
                        // return 'auto';
                    },
                    left:'50%'
                })
            }
        }else{//竖图
            if(realHeight < 100){//小图
                $(img).parent().parent().parent().find('.original img').width(50);
                $(img).parent().parent().parent().find('.original img').height('auto');
                $(img).parent().parent().parent().find('.original').css({
                    //此处需结合实际情况计算 上偏移：.original实际高度的二分之一
                    marginTop: function(){
                        return -(25*realHeight/realWidth)-36;
                    },
                    marginTop: function(){
                        return (10*realHeight/realWidth);
                    },
                    top:'50%'
                });
                $(img).parent().parent().parent().find('.original b').css('display','block')
            }else{
                $(img).parent().parent().parent().find('.original img').width(260);
                $(img).parent().parent().parent().find('.original img').height('auto');
                $(img).parent().parent().parent().find('.original').css({
                    //此处需结合实际情况计算 上偏移：.original实际高度的二分之一
                    marginTop: function(){
                        return -(130*realHeight/realWidth)-36;
                    },
                    top:'50%'
                });
                $(img).parent().parent().parent().find('.original b').css('display','block')
            }
        }
});


$('.piclist li.pic').hover(function(){
    $(this).addClass('on');
},function(){
    $(this).removeClass('on');
})

$(".piclist ul li:nth-child(4n)").addClass('r');
}

