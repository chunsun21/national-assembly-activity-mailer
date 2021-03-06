<div id="contents">
	<div id="person_img"></div>
	<div id="top"></div>
	<div id="description">
		<div class="desc_text">우리 동네 국회의원의 한 주가 궁금하다면, 코드포여의도에서 메일링을 받아보세요.<br/>내가 받아보고 싶은 국회의원을 선택해 이번 주 발의된 법안,<br/>통과된 법안, 국회의원의 국회 출석률까지 알 수 있습니다.</div>
	</div>
	<img id="submit_btn" src='/images/main/signup_btn.png'>
</div>

<div id="submit_pop">
	<img id="close_btn" src="/images/submit/x_btn.png">
	<div id="submit_title">메일링 신청하기</div>
	<div id="input"><input id="name" type="text" placeholder="이름"> <input id="email" type="text" placeholder="메일주소"></div>
	<div id="address_title">알고 싶은 의원 지역구</div>
	<div id="select">
		<div class="select_outer"><select id="City">
			<option value="none">시/도</option>
			<?$select_qry=sqlsrv_query($connect, "SELECT CityCompare FROM DistrictInfo GROUP BY CityCompare");
				while($row=sqlsrv_fetch_array($select_qry)){?>
			<option value="<?=$row['CityCompare']?>"><?=$row['CityCompare']?></option>
			<?}?>
		</select></div>
		<div class="select_outer"><select id="Dist"><option value="none">시/군/구</option></select></div>
		<div class="select_outer"><select id="Towns"><option value="none">읍/면/동</option></select></div>
	</div>
	<div id="person_description"></div>
	<img id="apply_btn" src="/images/submit/apply_btn.png">
</div>
<div id="person_pop"></div>
<div id="pop_back"></div>

<script>
$(document).ready(function(){
	$('#submit_btn').click(function(){
		$('#submit_pop,#person_pop,#pop_back').show();
	});

	$('#close_btn').click(function(){
		$('#submit_pop,#person_pop,#pop_back').hide();
	});

	var email_chk = 'no';
	var regExp = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
	$('#email').focusout(function(){
		if(!regExp.test($('#email').val())){
			$('#email').val('').attr('placeholder','올바른 메일주소를 입력해주세요.').addClass('err_select');
			email_chk = 'no';
		} else {
			$('#email').attr('placeholder','메일주소').removeClass('err_select');
			email_chk = 'ok';
		}
	});

	var dist_chk = 'no';
	var person_info;
	$('select#City').change(function(){
		if($(this).find('option:selected').val() != 'none'){
			var filter_array = {
				city: $(this).find('option:selected').val()
			};
			var filter = JSON.stringify(filter_array);

			load_ajax('/includes/ajax_loads/district.php?proc=city', 'select#Dist', filter);
		}
		dist_chk = 'no';
	});
	$('select#Dist').change(function(){
		if($(this).find('option:selected').val() != 'none'){
			var filter_array = {
				city: $('select#City').find('option:selected').val(),
				dist: $(this).find('option:selected').val()
			};
			var filter = JSON.stringify(filter_array);

			load_ajax('/includes/ajax_loads/district.php?proc=dist', 'select#Towns', filter);
		}
		dist_chk = 'no';
	});
	$('select#Towns').change(function(){
		dist_chk = 'no';
		if($(this).find('option:selected').val() != 'none'){
			var filter_array = {
				city: $('select#City').find('option:selected').val(),
				doffi: $(this).find('option:selected').val()
			};
			var filter = JSON.stringify(filter_array);

			$.post('/includes/ajax_loads/district.php?proc=person', {'time': timestamp, 'filter': filter}, function(data){
				person_info = data.split('||+=+||');
				$('div#person_description').html(person_info[0] + ' <span>/</span> ' + person_info[1] + ' <span>/</span> ' + person_info[2]);
				$('div#person_pop').css('background-image','url("' + person_info[3] + '")');
				dist_chk = 'ok';
			});
		}
	});

	$('#apply_btn').click(function(){
		if($('input#name').val() == ''){
			alert('이름을 입력해주세요.');
			$('input#name').focus();
			return false;
		}

		if(email_chk != 'ok'){
			alert('메일 주소를 정확히 입력해주세요.');
			$('input#email').val('').focus();
			return false;
		}

		if(dist_chk != 'ok'){
			alert('지역구를 정확히 선택해주세요.');
			return false;
		}

		var filter_array = {
			name: $('input#name').val(),
			email: $('input#email').val(),
			city: $('select#City').find('option:selected').val(),
			doffi: $('select#Towns').find('option:selected').val()
		};
		var filter = JSON.stringify(filter_array);

		$.post('/includes/ajax_loads/submit.php', {'time': timestamp, 'filter': filter}, function(data){
			if(data == 'ok'){
				$('div#person_img').css({'background':'none','background-image':'url("' + person_info[3] + '")',"background-repeat":"no-repeat","background-position-x":"center","background-position-y":"center","background-size":"cover"});
				$('div#top').attr('id','top_done');
				$('div#description').attr('id','description_done').html('<div>' + person_info[0] + ' / ' + person_info[1] + ' / ' + person_info[2] + '<br/><div id="emphasize">이제 ' + person_info[0] + ' 의원의 생활통지표를 받아보실 수 있습니다.</div>' + $('input#name').val() + '<img src="/images/done/mail_icon.png">' + $('input#email').val() + '<br/><div id="min_text">' + person_info[0] + ' 의원이 얼마나 성실히 의정활동을 하는지<br/>출석률, 의안 발의, 의안 찬반 등으로 꼼꼼히 지켜봐주세요.<br/>부디, 우리 ' + person_info[0] + ' 의원님을 잘 부탁드립니다.</div>');
				$('img#submit_btn').attr('src','/images/done/main_btn.png').off('click').click(function(){
					location.reload();
				});
				$('#close_btn').click();
			} else {
				alert(data);
			}
		});
	});
});
</script>