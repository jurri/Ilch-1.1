update ic1_posts
   set txt = replace(txt, 'ü', '�')
 where txt like '%ü%';
      
update ic1_posts
   set txt = replace(txt, 'ä', '�')
 where txt like '%ä%';

update ic1_posts
   set txt = replace(txt, 'ö', '�')
 where txt like '%ö%';

update ic1_posts
   set txt = replace(txt, 'ß', '�')
 where txt like '%ß%';

update ic1_posts
   set txt = replace(txt, 'Ã', '')
 where txt like '%Ã%';

update ic1_posts
   set txt = replace(txt, '¼', '�')
 where txt like '%¼%';

update ic1_posts
   set txt = replace(txt, '¶', '�')
 where txt like '%¶%';

update ic1_posts
   set txt = replace(txt, '¤', '�')
 where txt like '%¤%';