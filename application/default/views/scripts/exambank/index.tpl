<div id='main-header'><h1></h1></div>
<div class="section">
  <h2>Exambank</h2>
  <form id='courses'>
    <script language='Javascript' type='text/javascript'>
        // Create a new 'CourseSelector' object
        // Pass it the id of the form
        var selector = new CourseSelector('courses', '{$baseUrl}/exambank/courses');

        document.write('<p>');

        // Output the prefix select box
        selector.makePrefixSelection();

        // Output the course number select box
        selector.makeCourseSelection();

        document.write('</p><p>Course title: ');

        // Output the title as a string of (dynamic) text
        selector.makeTitleLine();

        document.write('</p>');
    </script>
  </form>

  <iframe width='500' height='400' src='' id='sched' style='border:0px; padding:0px; margin:0px;'></iframe>

  <script language='Javascript' type='text/javascript' >
      // selector.onChange gets called whenever the course selection changes
      selector.onChange = function ( prefix, course )
      {ldelim}
	    var sched = document.getElementById( 'sched' );
	    if( prefix != 0 & course != 0 ) {ldelim}
	      sched.src = '{$baseUrl}/exambank/exams/' + prefix + '/' + course;
	    {rdelim} else {ldelim}
	      sched.src = '';
	    {rdelim}
      {rdelim};
  </script>
</div>
