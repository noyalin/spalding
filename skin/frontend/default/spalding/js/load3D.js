function getByClass(oParent, sClass)
{
    var aEle=oParent.getElementsByTagName('*');
    var aResult=[];

    for(var i=0;i<aEle.length;i++)
    {
        if(aEle[i].className==sClass)
        {
            aResult.push(aEle[i]);
        }
    }

    return aResult;
}

window.onload=function (){
    var oDiv=document.getElementById('playimages');

    var oBtnPrev=getByClass(oDiv, 'prev')[0];
    var oBtnNext=getByClass(oDiv, 'next')[0];
    var oBtnN=getByClass(oDiv, 'prev_n')[0];
    var oBtnZ=getByClass(oDiv, 'next_z')[0];
    var oBtnZ_top=getByClass(oDiv, 'next_z_top')[0];
    var oBtnN_top=getByClass(oDiv, 'prev_n_top')[0];
    var oMarkLeft=getByClass(oDiv, 'mark_left')[0];
    var oMarkRight=getByClass(oDiv, 'mark_right')[0];


    var oUlBig=getByClass(oDiv, 'big_pic')[0];
    var aLiBig=oUlBig.getElementsByTagName('li');

    var nowZIndex=2;
    var now=0;
    var timer;

    function tab(){
        aLiBig[now].style.zIndex=nowZIndex++;

        if (now % 8 == 0) {
            clearInterval(timer);
        }
    }

    function tab_1(){

        aLiBig[now].style.zIndex=nowZIndex++;

        if (now % 8 == 0) {
            clearInterval(timer);
        }
    }

    oBtnPrev.onclick=function (){
        now--;
        if(now==-1)
        {
            now=aLiBig.length-1;
        }

        tab_1();
    };

    oBtnNext.onclick=function (){
        now++;
        if(now==aLiBig.length)
        {
            now=0;
        }


        tab();
    };

    oBtnN.onclick = function(){
        //timer=setInterval(oBtnNext.onclick, 60);

        //oBtnN_top.style.display = "block";
        //setTimeout(function(){
        //    oBtnN_top.style.display = "none";
        //},600);
        oBtnNext.onclick();
    };

    oBtnZ.onclick = function(){
        //timer=setInterval(oBtnPrev.onclick, 60);
        
        //oBtnZ_top.style.display = "block";
        //setTimeout(function(){
        //    oBtnZ_top.style.display = "none";
        //},600);
        oBtnPrev.onclick();
    };
};
